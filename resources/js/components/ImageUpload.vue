<script setup lang="ts">
/**
 * ImageUpload/Index.vue
 *
 * Stack: Vue 3 Composition API (TypeScript) · Inertia.js 3 · Tailwind CSS · Dropzone.js
 *
 * Install dependencies (if not already present):
 *   npm install dropzone
 *   npm install --save-dev @types/dropzone
 *   npm install @inertiajs/vue3   (Inertia 3)
 *
 * NOTE: No Ziggy required. HTTP calls use useHttp() from Inertia 3,
 *       which is Inertia's built-in fetch wrapper (CSRF-aware, no axios needed).
 */
import { usePage } from '@inertiajs/vue3';

import { useHttp } from '@inertiajs/vue3';
import Dropzone from 'dropzone';
import type { DropzoneFile, DropzoneOptions } from 'dropzone';
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import type { Ref } from 'vue';

import image from '@/routes/image';

// ─── Types ────────────────────────────────────────────────────────────────────

/** Shape of the JSON response returned by Laravel's upload endpoint. */
interface UploadResponse {
    success: boolean;
    path: string;
    url: string;
    name: string;
    size: number;
}

/** A successfully uploaded image tracked in Vue state. */
interface UploadedFile {
    id: string | number; // crypto.randomUUID()
    name: string;
    url: string; // public storage URL
    path: string | number; // storage-relative path, e.g. "uploads/abc.jpg"
    size: number; // bytes
}

/** Dropzone fires its error callback with either a string or an Error-like object. */
type DropzoneErrorMessage = string | { message: string };

// ─── Inertia 3 HTTP client ────────────────────────────────────────────────────
// useHttp() returns a fetch-like client that automatically:
//   • Attaches X-CSRF-TOKEN (reads from the <meta name="csrf-token"> tag)
//   • Sends X-Inertia headers
//   • Works with Laravel's JSON responses

const http = useHttp();

// ─── Constants ────────────────────────────────────────────────────────────────
const MAX_FILES = 10 as const;
const MAX_FILE_SIZE_MB = 5 as const; // per file, in MB
const ACCEPTED_MIME =
    'image/jpeg,image/png,image/gif,image/webp,image/svg+xml' as const;

// ─── State ───────────────────────────────────────────────────────────────────
const dropzoneEl: Ref<HTMLElement | null> = ref(null);
const dzInstance: Ref<Dropzone | null> = ref(null);
const uploadedFiles: Ref<UploadedFile[]> = ref([]);
const errorMessage: Ref<string> = ref('');
const isDragging: Ref<boolean> = ref(false);

/**
 * Tracks files that have been accepted by Dropzone and are currently
 * in-flight (uploading) but not yet resolved into `uploadedFiles`.
 * This is the key to enforcing the total cap across batches:
 *   totalOccupied = uploadedFiles.length + pendingCount
 */
const pendingCount: Ref<number> = ref(0);

const totalCount = computed<number>(
    () => uploadedFiles.value.length + pendingCount.value,
);
const isLimitReached = computed<boolean>(() => totalCount.value >= MAX_FILES);
const slotsRemaining = computed<number>(() =>
    Math.max(0, MAX_FILES - totalCount.value),
);

// 1. Define strict TypeScript interfaces for your data
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
    item?: {
        images?: ImageItem[];
    };
    modelType: string;
    collection?: string;
    modelId?: number;
    maxFilesize?: number;
    maxFiles?: number;
}

const props = withDefaults(defineProps<Props>(), {
    item: () => ({ images: [] }),
    collection: '',
    maxFilesize: 1024,
    maxFiles: 10,
});

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

/**
 * UUID v4 generator safe for all browsing contexts (HTTP localhost included).
 * Prefers crypto.randomUUID() when available (HTTPS / secure context),
 * and falls back to a crypto.getRandomValues() implementation otherwise.
 */
const generateUUID = (): string => {
    if (
        typeof crypto !== 'undefined' &&
        typeof crypto.randomUUID === 'function'
    ) {
        return crypto.randomUUID();
    }

    // Fallback: RFC-4122 v4 UUID via getRandomValues (works on plain HTTP)
    const bytes = new Uint8Array(16);
    crypto.getRandomValues(bytes);

    // Version 4 bits
    bytes[6] = (bytes[6] & 0x0f) | 0x40;
    // RFC 4122 variant bits
    bytes[8] = (bytes[8] & 0x3f) | 0x80;

    const hex = Array.from(bytes).map((b) => b.toString(16).padStart(2, '0'));

    return [
        hex.slice(0, 4).join(''),
        hex.slice(4, 6).join(''),
        hex.slice(6, 8).join(''),
        hex.slice(8, 10).join(''),
        hex.slice(10, 16).join(''),
    ].join('-');
};

/** Safely read the CSRF token from the standard Laravel meta tag. */
const getCsrfToken = (): string => (usePage().props as any).auth.token;
//   document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
//     ?.getAttribute('content') ?? ''

// ─── Dropzone Setup ──────────────────────────────────────────────────────────
onMounted((): void => {
    if (!dropzoneEl.value) {
        return;
    }

    Dropzone.autoDiscover = false;

    props.item.images?.forEach((image) => {
        uploadedFiles.value.push({
            id: image.img.uuid,
            name: image.img.name,
            url: image.img.original_url,
            path: image.img.id,
            size: image.img.size,
        });
    });

    const options: DropzoneOptions = {
        // ── Upload target ──────────────────────────────────────────────────────
        // Dropzone handles the multipart/form-data POST itself.
        // useHttp() is used only for non-file requests (delete).
        url: image.store.url,
        method: 'post',
        paramName: 'file',

        // ── Limits ────────────────────────────────────────────────────────────
        maxFiles: MAX_FILES,
        maxFilesize: MAX_FILE_SIZE_MB,
        acceptedFiles: ACCEPTED_MIME,

        // ── Behaviour ─────────────────────────────────────────────────────────
        uploadMultiple: false, // one file per POST → simpler server-side handling
        parallelUploads: 3,
        autoProcessQueue: true,
        addRemoveLinks: false, // custom remove UI rendered in the Vue template
        previewsContainer: false, // suppress Dropzone's own preview DOM

        // ── Headers ───────────────────────────────────────────────────────────
        // Inertia 3's useHttp() injects CSRF automatically for http.* calls,
        // but Dropzone's own XHR needs the token injected here manually.
        headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
        },
    };

    // ── Lifecycle hooks ───────────────────────────────────────────────────
    // init(this: Dropzone): void {

    // ── Instantiate Dropzone, then register all events directly on the instance ──
    // This avoids the `init(this: Dropzone)` pattern entirely, which ESLint flags
    // with @typescript-eslint/no-this-alias when you write `const dz = this`.
    dzInstance.value = new Dropzone(dropzoneEl.value, options);

    const dz = dzInstance.value; // ✅ local alias of the instance ref — not `this`

    // const dz = this;

    dz.on('dragenter', (): void => {
        isDragging.value = true;
    });
    dz.on('dragleave', (): void => {
        isDragging.value = false;
    });
    dz.on('drop', (): void => {
        isDragging.value = false;
    });

    /**
     * `accept` is called once per file BEFORE Dropzone queues it for upload.
     * Calling done(errorMessage) rejects the file; calling done() accepts it.
     *
     * This is the correct hook to enforce the total cap, because:
     *  - `maxFiles` only counts Dropzone's internal list, which we clear after
     *    each success via dz.removeFile() — so it can't reliably track totals.
     *  - `addedfile` fires AFTER the file is already queued; removing it there
     *    is too late for parallel batch uploads.
     *
     * We track `pendingCount` (in-flight) + `uploadedFiles.length` (done)
     * to get the true occupied slot count across any batch size.
     */
    dz.on('addedfile', (file: DropzoneFile): void => {
        clearError();

        // totalCount = already uploaded + currently uploading
        if (totalCount.value >= MAX_FILES) {
            errorMessage.value =
                `Maximum ${MAX_FILES} images allowed. ` +
                `${slotsRemaining.value === 0 ? 'No' : slotsRemaining.value} slot(s) remaining.`;
            dz.removeFile(file);

            return;
        }

        // Reserve a slot immediately so subsequent files in the same batch
        // see the updated count before the first upload resolves.
        pendingCount.value++;
    });

    dz.on('sending', (file: any, xhr: any, formData: any) => {
        formData.append('modelType', props.modelType);
        formData.append('modelId', props.modelId?.toString());
        formData.append('collection', props.collection);
    });

    dz.on('success', (file: DropzoneFile, response: string | object): void => {
        const data = response as UploadResponse;

        uploadedFiles.value.push({
            id: generateUUID(),
            name: data.name ?? file.name,
            url: data.url,
            path: data.path,
            size: data.size ?? file.size ?? 0,
        });

        // Release the pending slot — it is now a resolved uploaded slot
        pendingCount.value = Math.max(0, pendingCount.value - 1);

        // Clean up Dropzone's internal list (we manage our own state)
        dz.removeFile(file);

        if (isLimitReached.value) {
            errorMessage.value = `You've reached the ${MAX_FILES}-image limit.`;
        }
    });

    dz.on(
        'error',
        (file: DropzoneFile, message: DropzoneErrorMessage): void => {
            // Release the reserved slot on failure so the user can try again
            pendingCount.value = Math.max(0, pendingCount.value - 1);

            errorMessage.value =
                typeof message === 'string'
                    ? message
                    : (message.message ?? 'Upload failed. Please try again.');

            dz.removeFile(file);
        },
    );
    // },
    // };

    // .value = new Dropzone(dropzoneEl.value, options);
});

onBeforeUnmount((): void => {
    dzInstance.value?.destroy();
});

// ─── Actions ─────────────────────────────────────────────────────────────────

/**
 * Delete a single uploaded image.
 * useHttp() from Inertia 3 automatically handles CSRF & Inertia headers.
 */
const removeImage = async (file: UploadedFile): Promise<void> => {
    clearError();
    console.log(file.path);

    try {
        await http.delete(image.destroy.url({ id: file.path }));
    } catch {
        // Silently remove from UI even if the server-side delete fails
    }

    uploadedFiles.value = uploadedFiles.value.filter(
        (f: UploadedFile) => f.id !== file.id,
    );

    if (uploadedFiles.value.length < MAX_FILES) {
        clearError();
    }
};

/** Remove every uploaded image one by one. */
const clearAll = async (): Promise<void> => {
    clearError();
    pendingCount.value = 0;

    for (const file of [...uploadedFiles.value]) {
        await removeImage(file);
    }
};
</script>

<template>
    <!-- ── Page wrapper ─────────────────────────────────────────────────────── -->
    <div
        class="flex min-h-screen flex-col items-center bg-[#0d0f14] px-4 py-14 font-sans text-white"
    >
        <!-- ── Header ─────────────────────────────────────────────────────────── -->
        <header class="mb-10 text-center">
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
            <p class="mt-3 text-sm tracking-wide text-zinc-400">
                Drop up to
                <span class="font-semibold text-violet-300"
                    >{{ MAX_FILES }} images</span
                >
                &bull; JPG, PNG, GIF, WebP, SVG &bull; max
                {{ MAX_FILE_SIZE_MB }} MB each
            </p>
        </header>

        <!-- ── Upload card ────────────────────────────────────────────────────── -->
        <div class="w-full max-w-2xl">
            <!-- Dropzone area -->
            <div
                ref="dropzoneEl"
                :class="[
                    'relative flex flex-col items-center justify-center gap-4 ',
                    'cursor-pointer rounded-2xl border-2 border-dashed ',
                    'px-8 py-14 transition-all duration-300 ease-out',
                    isDragging
                        ? 'scale-[1.02] border-fuchsia-400 bg-fuchsia-500/10'
                        : isLimitReached
                          ? 'cursor-not-allowed border-zinc-700 bg-zinc-800/40 '
                          : 'border-zinc-600 bg-zinc-900/50 hover:border-violet-500 hover:bg-violet-500/5 ',
                ]"
            >

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
                    <span v-if="pendingCount > 0" class="ml-1 text-violet-400"
                        >(↑{{ pendingCount }})</span
                    >
                </span>

                <!-- Cloud icon -->
                <div
                    :class="[
                        'flex h-16 w-16 items-center justify-center rounded-full',
                        'transition-colors duration-300',
                        isDragging ? 'bg-fuchsia-500/20' : 'bg-zinc-800',
                        ]"
                >
                <svg
                        class="h-8 w-8"
                        :class="
                        isDragging ? 'text-fuchsia-400' : 'text-violet-400'
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
                
                
                
                <div class="pointer-events-none text-center select-none">
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
                                class="mt-4 flex w-full items-start gap-3 rounded-xl border border-red-700/50 bg-red-950/60 px-4 py-3 text-sm text-red-300"
                            >
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
                                <button
                                    @click="clearError"
                                    class="ml-auto transition-colors hover:text-white"
                                >
                                    ✕
                                </button>
                            </div>
                        </transition>

                    
                            <!-- ── Preview grid ────────────────────────────────────────────────────── -->
                            <transition-group
                                v-if="uploadedFiles.length"
                                tag="div"
                                name="grid-item"
                                class=" mt-10 grid w-full max-w-2xl grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 bg-violet-500/10 hover:cursor-default p-2 rounded-2xl border border-gray-300/10"
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
                                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                        loading="lazy"
                                    />
                    
                                    <!-- Overlay on hover -->
                                    <div
                                        class="absolute inset-0 flex flex-col justify-between bg-black/60 p-2 opacity-0 transition-opacity duration-200 group-hover:opacity-100"
                                    >
                                        <!-- File info -->
                                        <p
                                            class="truncate px-1 text-[10px] leading-tight font-medium text-white"
                                        >
                                            {{ file.name }}
                                        </p>
                                        <p class="px-1 text-[9px] text-zinc-400">
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
                    
                            <!-- ── Clear all ───────────────────────────────────────────────────────── -->
                            <transition
                                enter-active-class="transition duration-200 ease-out"
                                enter-from-class="opacity-0 translate-y-2"
                                enter-to-class="opacity-100 translate-y-0"
                            >
                                <div v-if="uploadedFiles.length" class="mt-8 flex justify-center">
                                    <button
                                        @click="clearAll"
                                        class="text-xs text-zinc-500 underline underline-offset-4 transition-colors duration-150 hover:text-red-400"
                                    >
                                        Clear all uploads
                                    </button>
                                </div>
                            </transition>
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
