<script setup lang="ts">
/**
 * ImageUpload/Index.vue
 *
 * Stack: Vue 3 Composition API (TypeScript) · Inertia.js 3 · Tailwind CSS · FilePond (vue-filepond)
 *
 * FilePond is used as a headless upload engine (queue, validation, parallel
 * uploads, XHR + progress). Its own UI is visually hidden (`sr-only`), so the
 * drop area, preview grid, per-image progress bars, badge and error banner
 * are our own markup.
 *
 * NOTE: No Ziggy required. Delete calls use useHttp() from Inertia 3.
 */
import { useHttp, usePage } from '@inertiajs/vue3';
import type { FilePondFile } from 'filepond';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import vueFilePond from 'vue-filepond';
import { ref, computed, onBeforeUnmount } from 'vue';
import type { Ref } from 'vue';

// Not needed while FilePond is hidden, but handy if you ever un-hide it for debugging.
import 'filepond/dist/filepond.min.css';

import image from '@/routes/image';

// ─── FilePond component ───────────────────────────────────────────────────────
const FilePond = vueFilePond(
    FilePondPluginFileValidateType,
    FilePondPluginFileValidateSize,
);

/** The few instance methods we call on the FilePond component ref. */
interface FilePondInstance {
    browse(): void;
    addFiles(files: File[]): void;
    removeFile(id: string): void;
    removeFiles(): void;
}

/** Error shapes FilePond passes around (validation, upload, plugin errors). */
interface FilePondErrorLike {
    main?: string;
    sub?: string;
    body?: string;
    status?: { main?: string; sub?: string };
}

// ─── Types ────────────────────────────────────────────────────────────────────

/** Shape of the JSON response returned by Laravel's upload endpoint. */
interface UploadResponse {
    success: boolean;
    imageId: string;
    url: string;
    name: string;
    size: number;
    id: number;
}

/** A tile in the grid: either an existing/finished image or one still uploading. */
interface UploadedFile {
    id: string | number; // uploading tiles use the FilePond file id
    name: string;
    url: string; // server URL (existing images) or local preview (this session)
    imageId: string | number; // server id (empty while uploading)
    size: number; // bytes
    status: 'uploading' | 'done';
    progress: number; // 0–100
    blobUrl?: string; // local preview, revoked on remove / unmount
}

interface ImageItem {
    img: {
        id: string | number;
        uuid: string | number;
        name: string;
        size: number;
        mime_type: string;
        original_url: string;
    };
}

interface Props {
    images?: ImageItem[];
    modelType: string;
    collection?: string;
    modelId?: number;
    maxFilesize?: number;
    maxFiles?: number;
}

const props = withDefaults(defineProps<Props>(), {
    images: () => [],
    collection: '',
    maxFilesize: 1024,
    maxFiles: 10,
});

// ─── Inertia 3 ────────────────────────────────────────────────────────────────
const http = useHttp();
const page = usePage();

// ─── Constants ────────────────────────────────────────────────────────────────
const MAX_FILES = 10 as const;
const MAX_FILE_SIZE_MB = 5 as const; // per file, in MB
const ACCEPTED_MIME: string[] = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'image/svg+xml',
];

// ─── State ───────────────────────────────────────────────────────────────────
const pond: Ref<FilePondInstance | null> = ref(null);

// Existing images coming from the server are shown immediately.
const uploadedFiles: Ref<UploadedFile[]> = ref(
    props.images.map(
        (item): UploadedFile => ({
            id: item.img.uuid,
            name: item.img.name,
            url: item.img.original_url,
            imageId: item.img.id,
            size: item.img.size,
            status: 'done',
            progress: 100,
        }),
    ),
);
const errorMessage: Ref<string> = ref('');
const isDragging: Ref<boolean> = ref(false);

/**
 * FilePond item ids that hold a slot: accepted and still uploading (or
 * finishing). A slot is released when the tile becomes "done", fails, or is
 * cancelled. Tracking ids makes releasing idempotent, so several FilePond
 * events can safely release the same file:
 *   totalOccupied = finished images + pendingIds.size
 */
const pendingIds: Ref<Set<string>> = ref(new Set<string>());
const pendingCount = computed<number>(() => pendingIds.value.size);

const doneCount = computed<number>(
    () => uploadedFiles.value.filter((f) => f.status === 'done').length,
);
const totalCount = computed<number>(() => doneCount.value + pendingCount.value);
const isLimitReached = computed<boolean>(() => totalCount.value >= MAX_FILES);
const slotsRemaining = computed<number>(() =>
    Math.max(0, MAX_FILES - totalCount.value),
);
const isUploading = computed<boolean>(() =>
    uploadedFiles.value.some((f) => f.status === 'uploading'),
);

// ─── Helpers ─────────────────────────────────────────────────────────────────

const formatBytes = (bytes: number): string => {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

const clearError = (): void => {
    errorMessage.value = '';
};

const release = (id?: string): void => {
    if (id) {
        pendingIds.value.delete(id);
    }
};

const findItem = (id: string | number): UploadedFile | undefined =>
    uploadedFiles.value.find((f) => f.id === id);

/** Remove an uploading tile (failed / cancelled) and free its slot. */
const dropItem = (id: string): void => {
    const item = findItem(id);

    if (item?.blobUrl) {
        URL.revokeObjectURL(item.blobUrl);
    }

    uploadedFiles.value = uploadedFiles.value.filter((f) => f.id !== id);
    release(id);
};

const getCsrfToken = (): string =>
    (page.props as unknown as { auth: { token: string } }).auth.token;

/** Turn a FilePond error (validation / upload) into a readable string. */
const describeError = (
    error: FilePondErrorLike | string | null | undefined,
    fallback: string,
): string => {
    if (!error) {
        return fallback;
    }

    if (typeof error === 'string') {
        return error;
    }

    const main = error.main ?? error.status?.main;
    const sub = error.sub ?? error.status?.sub;

    if (main) {
        return sub ? `${main}. ${sub}` : main;
    }

    return error.body ?? fallback;
};

/** Pull the message out of a Laravel JSON error response (e.g. a 422). */
const parseServerError = (raw: string): string => {
    const fallback = 'Upload failed. Please try again.';

    try {
        const json = JSON.parse(raw) as {
            message?: string;
            errors?: Record<string, string[]>;
        };
        const firstError = json.errors
            ? Object.values(json.errors)[0]?.[0]
            : undefined;

        return json.message ?? firstError ?? fallback;
    } catch {
        return raw || fallback;
    }
};

// ─── FilePond config ─────────────────────────────────────────────────────────

// Same request contract as the Dropzone version: multipart POST, field "file",
// extra fields modelType / modelId / collection, CSRF + JSON headers.
// No `revert` is configured on purpose: deletes go through removeImage().
const server = {
    process: {
        url: image.store.url(),
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
        ondata: (formData: FormData): FormData => {
            formData.append('modelType', props.modelType);

            if (props.modelId !== undefined) {
                formData.append('modelId', String(props.modelId));
            }

            formData.append('collection', props.collection);

            return formData;
        },
        // Whatever is returned here becomes `file.serverId`.
        // We return the raw JSON so onProcessFile can parse it.
        onload: (response: string): string => response,
        onerror: (response: string): string => parseServerError(response),
    },
};

/**
 * Runs for every file BEFORE it is accepted. Enforces the total cap across
 * batches: finished + currently uploading. The check and the slot reservation
 * happen synchronously, so files from the same batch see the updated count.
 */
const beforeAddFile = (item: { id: string }): boolean => {
    clearError();

    if (totalCount.value >= MAX_FILES) {
        errorMessage.value =
            `Maximum ${MAX_FILES} images allowed. ` +
            `${slotsRemaining.value === 0 ? 'No' : slotsRemaining.value} slot(s) remaining.`;

        return false;
    }

    pendingIds.value.add(item.id);

    return true;
};

/**
 * File accepted: show it in the grid right away with a local preview
 * (the upload starts automatically). On failure (type / size) free the slot.
 */
const onAddFile = (error: unknown, file?: FilePondFile): void => {
    if (error || !file) {
        release(file?.id);

        return;
    }

    const blobUrl = URL.createObjectURL(file.file);

    uploadedFiles.value.push({
        id: file.id,
        name: file.filename,
        url: blobUrl,
        imageId: '',
        size: file.fileSize,
        status: 'uploading',
        progress: 0,
        blobUrl,
    });
};

/** Live upload progress for one file (FilePond reports 0–1). */
const onProcessFileProgress = (file: FilePondFile, progress: number): void => {
    const item = findItem(file.id);

    if (item && Number.isFinite(progress)) {
        item.progress = Math.min(100, Math.round(progress * 100));
    }
};

/** Validation or upload error: remove the tile, free the slot, show the message. */
const onError = (
    error: FilePondErrorLike | string | null,
    file?: FilePondFile,
): void => {
    if (file) {
        dropItem(file.id);
    }

    const prefix = file?.filename ? `${file.filename}: ` : '';

    errorMessage.value =
        prefix + describeError(error, 'Upload failed. Please try again.');
};

/** Upload finished (successfully or not). */
const onProcessFile = (error: unknown, file: FilePondFile): void => {
    const { id, serverId } = file;

    // We manage our own list, so FilePond's copy is no longer needed.
    pond.value?.removeFile(id);

    if (error) {
        dropItem(id); // message already shown by onError

        return;
    }

    const item = findItem(id);

    if (!item) {
        return; // cancelled while finishing
    }

    let data: UploadResponse;

    try {
        data = JSON.parse(serverId) as UploadResponse;
    } catch {
        dropItem(id);
        errorMessage.value = 'Unexpected response from the server.';

        return;
    }

    // Done: the tile switches to its final state immediately. The local preview
    // stays as the thumbnail, so the full-size image is NOT downloaded again.
    item.imageId = data.id;
    item.name = data.name ?? item.name;
    item.size = data.size ?? item.size;
    item.progress = 100;
    item.status = 'done';

    release(id);

    if (isLimitReached.value) {
        errorMessage.value = `You've reached the ${MAX_FILES}-image limit.`;
    }
};

// ─── Drag & drop / browse (our own drop area) ────────────────────────────────
let dragDepth = 0; // dragenter/leave fire for child elements too

const onDragEnter = (): void => {
    dragDepth++;
    isDragging.value = true;
};

const onDragLeave = (): void => {
    dragDepth = Math.max(0, dragDepth - 1);

    if (dragDepth === 0) {
        isDragging.value = false;
    }
};

const onDrop = (e: DragEvent): void => {
    dragDepth = 0;
    isDragging.value = false;

    const files = Array.from(e.dataTransfer?.files ?? []);

    if (files.length) {
        pond.value?.addFiles(files);
    }
};

const openBrowser = (): void => {
    if (isLimitReached.value) {
        errorMessage.value = `You've reached the ${MAX_FILES}-image limit.`;

        return;
    }

    pond.value?.browse();
};

// ─── Actions ─────────────────────────────────────────────────────────────────

/** Cancel an upload that is still in progress. */
const cancelUpload = (file: UploadedFile): void => {
    const id = String(file.id);

    pond.value?.removeFile(id); // aborts the XHR
    dropItem(id);
    clearError();
};

/**
 * Delete a single uploaded image.
 * useHttp() from Inertia 3 automatically handles CSRF & Inertia headers.
 */
const removeImage = async (file: UploadedFile): Promise<void> => {
    clearError();

    try {
        await http.delete(image.destroy.url({ id: file.imageId }));
    } catch {
        // Silently remove from UI even if the server-side delete fails
    }

    if (file.blobUrl) {
        URL.revokeObjectURL(file.blobUrl);
    }

    uploadedFiles.value = uploadedFiles.value.filter(
        (f: UploadedFile) => f.id !== file.id,
    );

    if (doneCount.value < MAX_FILES) {
        clearError();
    }
};

/** Cancel in-flight uploads, then remove every uploaded image one by one. */
const clearAll = async (): Promise<void> => {
    clearError();
    pond.value?.removeFiles();

    uploadedFiles.value
        .filter((f) => f.status === 'uploading')
        .forEach((f) => dropItem(String(f.id)));
    pendingIds.value.clear();

    for (const file of [...uploadedFiles.value]) {
        await removeImage(file);
    }
};

onBeforeUnmount((): void => {
    uploadedFiles.value.forEach((f) => {
        if (f.blobUrl) {
            URL.revokeObjectURL(f.blobUrl);
        }
    });
});
</script>

<template>
    <!-- ── Page wrapper ─────────────────────────────────────────────────────── -->
    <div
        class="flex min-h-screen flex-col items-center bg-[rgba(0,0,0,0.22)] px-4 py-12 font-sans text-white dark:bg-[rgba(0,0,0,0.73)]"
    >
        <!-- ── Header ─────────────────────────────────────────────────────────── -->
        <header class="mb-7 text-center">
            <h1
                class="text-4xl leading-none font-extrabold tracking-tight text-white"
            >
                Image
                <span
                    class="bg-linear-to-r from-violet-400 to-fuchsia-400 bg-clip-text text-transparent"
                >
                    Uploader
                </span>
            </h1>
            <p
                class="mt-3 rounded-full border border-gray-100/20 px-2 text-sm tracking-wide text-zinc-300 dark:text-zinc-400"
            >
                Drop up to
                <span class="font-semibold text-violet-300"
                    >{{ MAX_FILES }} images</span
                >
                &bull; JPG, PNG, GIF, WebP, SVG &bull; max
                {{ MAX_FILE_SIZE_MB }} MB each
            </p>
        </header>

        <!-- ── Upload card ────────────────────────────────────────────────────── -->
        <div class="w-full max-w-3xl">
            <div
                class="relative overflow-hidden rounded-2xl bg-black/50 p-0.5 focus:outline-none dark:bg-white/10"
            >
                <!-- Glow: smaller layer (same look) and paused while uploading -->
                <span
                    :class="isUploading ? '[animation-play-state:paused]' : ''"
                    class="absolute inset-[-200%] animate-spin bg-[conic-gradient(from_90deg_at_50%_50%,transparent_80%,#ffc400_90%,#d61900_100%)] blur-2xl"
                    style="animation-duration: 12s"
                />

                <!-- Drop area (click on empty space = browse, drop = upload) -->
                <div
                    :class="[
                        'relative flex flex-col items-center justify-center gap-4 backdrop-sepia backdrop-blur-3xl grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5',
                        'cursor-pointer rounded-2xl border-2 border-dashed',
                        'px-8 py-14 transition-all duration-300 ease-out',
                        isDragging
                            ? 'scale-[1.02] border-fuchsia-400 bg-fuchsia-500/10'
                            : isLimitReached
                              ? 'cursor-not-allowed border-zinc-700 bg-zinc-800/40'
                              : 'border-zinc-500 bg-zinc-900/50 hover:border-slate-400 hover:bg-zinc-950/60 dark:hover:border-violet-300/60',
                    ]"
                    @click.self="openBrowser"
                    @dragenter.prevent="onDragEnter"
                    @dragover.prevent
                    @dragleave.prevent="onDragLeave"
                    @drop.prevent="onDrop"
                >
                    <!-- FilePond engine (UI hidden — everything visible is ours) -->
                    <FilePond
                        ref="pond"
                        class="sr-only"
                        name="file"
                        allow-multiple
                        :allow-drop="false"
                        :allow-paste="false"
                        :instant-upload="true"
                        :max-parallel-uploads="3"
                        :accepted-file-types="ACCEPTED_MIME"
                        :max-file-size="`${MAX_FILE_SIZE_MB}MB`"
                        :server="server"
                        :before-add-file="beforeAddFile"
                        @addfile="onAddFile"
                        @processfileprogress="onProcessFileProgress"
                        @error="onError"
                        @processfile="onProcessFile"
                    />

                    <!-- Limit badge -->
                    <span
                        :class="[
                            'absolute top-3 right-3 rounded-full px-2.5 py-1 text-xs font-semibold',
                            isLimitReached
                                ? 'bg-red-900/60 text-red-300'
                                : 'bg-zinc-800 text-zinc-400',
                        ]"
                    >
                        {{ totalCount }} / {{ MAX_FILES }}
                        <span
                            v-if="pendingCount > 0"
                            class="ml-1 text-violet-400"
                            >(↑{{ pendingCount }})</span
                        >
                    </span>

                    <div
                        class="pointer-events-none col-span-2 text-center select-none sm:col-span-3 md:col-span-4 lg:col-span-5"
                    >
                        <!-- Cloud icon -->
                        <div
                            class="col-span-2 flex items-center justify-center sm:col-span-3 md:col-span-4 lg:col-span-5"
                        >
                            <div
                                :class="[
                                    'flex h-16 w-16 items-center justify-center rounded-full',
                                    'transition-colors duration-300',
                                    isDragging
                                        ? 'bg-fuchsia-500/20'
                                        : 'bg-zinc-800',
                                ]"
                            >
                                <svg
                                    class="h-8 w-8"
                                    :class="
                                        isDragging
                                            ? 'text-fuchsia-400'
                                            : 'text-violet-400'
                                    "
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 16v-8m0 0-3 3m3-3 3 3M6.75 19.5a4.5 4.5 0 0 1-1.632-8.685
                                      5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0
                                      0 1 18 19.5H6.75Z"
                                    />
                                </svg>
                            </div>
                        </div>
                        <p class="text-base font-semibold text-zinc-200">
                            {{
                                isDragging
                                    ? 'Release to upload'
                                    : 'Drag & drop your images here'
                            }}
                        </p>
                        <p class="mt-1 text-sm text-zinc-500">
                            or
                            <span
                                class="text-violet-400 underline underline-offset-2"
                                >browse</span
                            >
                            to choose files
                        </p>
                    </div>

                    <!-- Error banner -->
                    <transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 -translate-y-1"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <div
                            v-if="errorMessage"
                            class="col-span-2 mt-4 flex w-full items-start justify-between gap-3 rounded-xl border border-red-700/50 bg-red-950/60 px-4 py-3 text-sm text-red-300 sm:col-span-3 md:col-span-4 lg:col-span-5"
                        >
                            <div class="flex items-start gap-2">
                                <svg
                                    class="mt-0.5 h-4 w-4 shrink-0"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0zm-8-5a.75.75 0 0 1 .75.75v4.5a
                             .75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <span>{{ errorMessage }}</span>
                            </div>
                            <button
                                @click.stop="clearError"
                                class="transition-colors hover:text-white"
                            >
                                ✕
                            </button>
                        </div>
                    </transition>

                    <!-- ── Preview grid (uploading + finished) ───────────────── -->
                    <transition-group
                        v-if="uploadedFiles.length"
                        name="grid-item"
                    >
                        <div
                            v-for="file in uploadedFiles"
                            :key="file.id"
                            class="group relative aspect-square overflow-hidden rounded-xl bg-zinc-800 ring-1 ring-zinc-700 transition-all duration-200 hover:ring-violet-500"
                        >
                            <!-- Thumbnail -->
                            <img
                                :src="file.url"
                                :alt="file.name"
                                decoding="async"
                                :class="[
                                    'h-full w-full object-cover transition-all duration-300',
                                    file.status === 'uploading'
                                        ? 'opacity-50'
                                        : 'group-hover:scale-105',
                                ]"
                                :loading="
                                    file.status === 'uploading'
                                        ? 'eager'
                                        : 'lazy'
                                "
                            />

                            <!-- ▸ Uploading state: per-image progress bar + cancel -->
                            <template v-if="file.status === 'uploading'">
                                <button
                                    @click.stop="cancelUpload(file)"
                                    class="absolute top-1.5 right-1.5 rounded-full bg-black/60 p-1 text-zinc-200 transition-colors duration-150 hover:bg-red-600"
                                    title="Cancel upload"
                                >
                                    <svg
                                        class="h-3 w-3"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"
                                        />
                                    </svg>
                                </button>

                                <div
                                    class="absolute inset-x-0 bottom-0 bg-black/70 px-2 pt-1.5 pb-2"
                                >
                                    <div
                                        class="mb-1 flex items-center justify-between text-[10px] font-medium text-zinc-200"
                                    >
                                        <span>{{
                                            file.progress < 100
                                                ? 'Uploading…'
                                                : 'Finishing…'
                                        }}</span>
                                        <span>{{ file.progress }}%</span>
                                    </div>
                                    <div
                                        class="h-1.5 w-full overflow-hidden rounded-full bg-zinc-700"
                                    >
                                        <div
                                            class="h-full rounded-full bg-linear-to-r from-violet-400 to-fuchsia-400 transition-[width] duration-200 ease-out"
                                            :class="
                                                file.progress >= 100
                                                    ? 'animate-pulse'
                                                    : ''
                                            "
                                            :style="{
                                                width: file.progress + '%',
                                            }"
                                        />
                                    </div>
                                </div>
                            </template>

                            <!-- ▸ Finished state: hover overlay with info + remove -->
                            <div
                                v-else
                                class="absolute inset-0 flex flex-col justify-between bg-black/60 p-2 opacity-40 transition-opacity duration-200 group-hover:opacity-100 sm:opacity-0"
                            >
                                <!-- File info -->
                                <p
                                    class="truncate px-1 text-[10px] leading-tight font-medium text-white opacity-0 sm:opacity-100"
                                >
                                    {{ file.name }}
                                </p>
                                <p
                                    class="px-1 text-[9px] text-zinc-400 opacity-0 sm:opacity-100"
                                >
                                    {{ formatBytes(file.size) }}
                                </p>

                                <!-- Remove button -->
                                <button
                                    @click.stop="removeImage(file)"
                                    class="mt-auto self-end rounded-lg bg-red-600 p-1.5 text-white transition-colors duration-150 hover:bg-red-500"
                                    title="Remove image"
                                >
                                    <svg
                                        class="h-3.5 w-3.5"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75
                                       0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75
                                       2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03
                                       41.03 0 0 0 14 4.193v-.443A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0
                                       1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69
                                       0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58
                                       7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75
                                       0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </transition-group>

                    <!-- ── Clear all ─────────────────────────────────────────── -->
                    <transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 translate-y-2"
                        enter-to-class="opacity-100 translate-y-0"
                    >
                        <div
                            v-if="uploadedFiles.length"
                            class="col-span-2 mt-8 flex justify-center sm:col-span-3 md:col-span-4 lg:col-span-5"
                        >
                            <button
                                @click.stop="clearAll"
                                class="rounded border border-gray-300/10 px-2 text-xs text-zinc-400 underline underline-offset-4 transition-colors duration-150 hover:bg-black/50 hover:text-red-400 dark:text-zinc-500"
                            >
                                Clear all uploads
                            </button>
                        </div>
                    </transition>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ── Grid item transitions ──────────────────────────────────────────────── */
.grid-item-enter-active {
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.grid-item-leave-active {
    transition: all 0.2s ease-in;
}
.grid-item-enter-from {
    opacity: 0;
    transform: scale(0.75);
}
.grid-item-leave-to {
    opacity: 0;
    transform: scale(0.85);
}
</style>