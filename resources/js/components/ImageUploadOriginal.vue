<script setup lang="ts">
import { usePage, useHttp } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import Dropzone from 'dropzone';
import 'dropzone/dist/dropzone.css';
import type { DropzoneFile, DropzoneOptions } from 'dropzone';
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import type { Ref } from 'vue';

import image from '@/routes/image';

// ─── Types ────────────────────────────────────────────────────────────────────
interface UploadResponse {
    success: boolean;
    imageId: string;
    url: string;
    name: string;
    size: number;
    id: number;
}

interface UploadedFile {
    id: string | number;
    name: string;
    url: string;
    imageId: string | number;
    size: number;
}

/** Dropzone file with the extra fields we attach. */
type DzFile = DropzoneFile & {
    imageId?: string | number;
    isExisting?: boolean;
    _pending?: boolean;
};

type DropzoneErrorMessage = string | { message: string };

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
    images: () => ([]),
    collection: '',
    maxFilesize: 1024,
    maxFiles: 10,
});

const http = useHttp();

// ─── Constants ────────────────────────────────────────────────────────────────
const MAX_FILES = 10 as const;
const MAX_FILE_SIZE_MB = 5 as const;
const ACCEPTED_MIME =
    'image/jpeg,image/png,image/gif,image/webp,image/svg+xml' as const;

// ─── State ───────────────────────────────────────────────────────────────────
const dropzoneEl: Ref<HTMLElement | null> = ref(null);
const dzInstance: Ref<Dropzone | null> = ref(null);
const uploadedFiles: Ref<UploadedFile[]> = ref([]);
const errorMessage: Ref<string> = ref('');
const pendingCount: Ref<number> = ref(0);

const totalCount = computed<number>(
    () => uploadedFiles.value.length + pendingCount.value,
);
const isLimitReached = computed<boolean>(() => totalCount.value >= MAX_FILES);
const slotsRemaining = computed<number>(() =>
    Math.max(0, MAX_FILES - totalCount.value),
);

// ─── Helpers ─────────────────────────────────────────────────────────────────
const clearError = (): void => {
    errorMessage.value = '';
};

const generateUUID = (): string => {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }

    const bytes = new Uint8Array(16);
    crypto.getRandomValues(bytes);
    bytes[6] = (bytes[6] & 0x0f) | 0x40;
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

const getCsrfToken = (): string => (usePage().props as any).auth.token;

let isTearingDown = false;
// ─── Dropzone Setup ──────────────────────────────────────────────────────────
onMounted((): void => {
    if (!dropzoneEl.value) {
        return;
    }

    onMounted(() => {
        router.reload({ only: ['images'] }); // use the prop name your controller returns
    });

    (Dropzone as any).autoDiscover = false;

    const options: DropzoneOptions = {
        url: image.store.url(),
        method: 'post',
        paramName: 'file',

        maxFiles: MAX_FILES,
        maxFilesize: MAX_FILE_SIZE_MB,
        acceptedFiles: ACCEPTED_MIME,

        uploadMultiple: false,
        parallelUploads: 3,
        autoProcessQueue: true,
        addRemoveLinks: true, // native Dropzone remove link

        headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
        },
    };

    dzInstance.value = new Dropzone(dropzoneEl.value, options);
    const dz = dzInstance.value;

    // ── Before a new file is queued ──────────────────────────────────────
    dz.on('addedfile', (file: DzFile): void => {
        if (file.isExisting) {
            return; // preloaded image, already counted in uploadedFiles
        }

        clearError();

        if (totalCount.value >= MAX_FILES) {
            errorMessage.value = `Maximum ${MAX_FILES} images allowed.`;
            dz.removeFile(file);

            return;
        }

        file._pending = true;
        pendingCount.value++;
    });

    dz.on('sending', (file: any, xhr: any, formData: any) => {
        formData.append('modelType', props.modelType);
        formData.append('modelId', props.modelId?.toString() ?? '');
        formData.append('collection', props.collection);
    });

    dz.on('success', (file: DzFile, response: string | object): void => {
        const data = response as UploadResponse;

        file.imageId = data.id;
        file._pending = false;
        pendingCount.value = Math.max(0, pendingCount.value - 1);

        uploadedFiles.value.push({
            id: generateUUID(),
            name: data.name ?? file.name,
            url: data.url,
            imageId: data.id,
            size: data.size ?? file.size ?? 0,
        });

        if (isLimitReached.value) {
            errorMessage.value = `You've reached the ${MAX_FILES}-image limit.`;
        }
    });

    dz.on('error', (file: DzFile, message: DropzoneErrorMessage): void => {
        if (file._pending) {
            file._pending = false;
            pendingCount.value = Math.max(0, pendingCount.value - 1);
        }

        errorMessage.value =
            typeof message === 'string'
                ? message
                : (message.message ?? 'Upload failed. Please try again.');

        dz.removeFile(file);
    });

    // ── Remove (native "Remove file" link, removeFile(), removeAllFiles()) ──
    dz.on('removedfile', async (file: DzFile): Promise<void> => {
        if (isTearingDown) {
            return;
        }

        // Cancelled while still uploading
        if (file._pending) {
            file._pending = false;
            pendingCount.value = Math.max(0, pendingCount.value - 1);
        }

        // Rejected / failed files have no imageId → nothing to delete
        if (file.imageId === undefined || file.imageId === null) {
            return;
        }

        uploadedFiles.value = uploadedFiles.value.filter(
            (f) => f.imageId !== file.imageId,
        );

        if (uploadedFiles.value.length < MAX_FILES) {
            clearError();
        }

        try {
            await http.delete(image.destroy.url({ id: file.imageId }));
        } catch {
            // ignore server errors, UI is already updated
        }
    });

    // ── Load existing images as native previews ──────────────────────────
    props.images?.forEach((item) => {
        const mock: DzFile = {
            name: item.img.name,
            size: item.img.size,
            type: item.img.mime_type,
            accepted: true,
            status: Dropzone.SUCCESS,
            imageId: item.img.id,
            isExisting: true,
        } as DzFile;

        uploadedFiles.value.push({
            id: item.img.uuid,
            name: item.img.name,
            url: item.img.original_url,
            imageId: item.img.id,
            size: item.img.size,
        });

        dz.files.push(mock);
        dz.displayExistingFile(mock, item.img.original_url, () => { }, 'anonymous', true);
    });
});

onBeforeUnmount((): void => {
    isTearingDown = true;
    dzInstance.value?.destroy();
});
</script>

<template>


    <div >

        
        <!-- Counter -->
        <div class="flex items-center justify-center">
            <div
                class="rounded-full border border-gray-200/20 px-3 text-sm"
                :class="isLimitReached ? 'text-red-400' : 'text-yellow-200'"
            >
                {{ totalCount }} / {{ MAX_FILES }}
                <span v-if="pendingCount > 0" class="ml-1 text-violet-400">(↑{{ pendingCount }})</span>
            </div>
        </div>

        <!-- Counter -->
        <div class="flex items-center justify-center mt-3">
            <div class="rounded-full border border-gray-200/20 px-3 text-sm"
                :class="isLimitReached ? 'text-red-400' : 'text-yellow-200'">
                {{ $t("general.You can only upload") }}
                <span class="mx-1 rounded-full border border-gray-200/40 bg-zinc-900 px-2 text-white">
                    {{ slotsRemaining }}
                </span>
                {{ slotsRemaining === 1 ? $t("general.image") : $t("general.images") }}
                <span v-if="pendingCount > 0" class="ml-1 text-violet-400">(↑{{ pendingCount }})</span>
            </div>
        </div>

        <!-- Native Dropzone: previews are rendered inside this box -->
        <div ref="dropzoneEl" class="dropzone mt-3 bg-white shadow-xl">
            <div class="dz-message" data-dz-message>
                <div>Drop Images here to upload</div>
                <div>You can only upload {{ MAX_FILES }} images (max {{ MAX_FILE_SIZE_MB }} MB each)</div>
            </div>
        </div>

        <!-- Error banner -->
        <div v-if="errorMessage"
            class="mt-3 flex items-start justify-between gap-3 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
            <span>{{ errorMessage }}</span>
            <button type="button" class="hover:text-red-900" @click="clearError">✕</button>
        </div>
    </div>
</template>