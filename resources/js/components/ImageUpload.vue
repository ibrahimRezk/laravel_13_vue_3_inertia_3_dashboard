<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { useHttp } from '@inertiajs/vue3';

import Dropzone from 'dropzone';
import 'dropzone/dist/dropzone.css';

import { trans } from 'laravel-vue-i18n';
import { onMounted, ref, watch } from 'vue';
import image from '@/routes/image';

// 1. Define strict TypeScript interfaces for your data
interface ImageItem {
    img: {
        id: number;
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

// Dropzone instance reference for the watcher
let dzInstance: Dropzone | null = null;
const maxFilesNumber = ref(props.maxFiles);

// Calculate initial max files if items already exist
onMounted(() => {
    if (props.item?.images) {
        maxFilesNumber.value = props.maxFiles - props.item.images.length;
    }

    Dropzone.autoDiscover = false;


    dzInstance = new Dropzone('#image-upload', {
        url: image.store().url,
        // url: window.route('images.store'), // Assuming Ziggy is global
        headers: {
            // 'X-CSRF-TOKEN': (usePage().props as any).csrf_token,
            "X-CSRF-TOKEN": (usePage().props as any).auth.token

        },
        paramName: 'image',
        maxFilesize: props.maxFilesize,
        maxFiles: maxFilesNumber.value,
        addRemoveLinks: true,
        acceptedFiles: 'image/*',

        // Translations
        dictDefaultMessage: trans(
            'general.press here or drage and drop images here',
        ),
        dictFallbackMessage: trans(
            'general.Your browser does not support multi-image and drag and drop',
        ),
        dictInvalidFileType: trans(
            'general.You cannot upload this type of file',
        ),
        dictCancelUpload: trans('general.Cancel upload'),
        dictCancelUploadConfirmation: trans(
            'general.Are you sure you want to cancel the file upload?',
        ),
        dictMaxFilesExceeded: trans(
            'general.You cannot upload more than this number',
        ),
        dictFileTooBig: trans('general.File is too big'),
        dictRemoveFile: trans('general.delete'),

        // Handles file removal
        removedfile: function (file: any) {
            if (file.previewElement) {
                file.previewElement.remove();
            }

            if (file.image_id) {


                const http = useHttp();

                http.delete(image.delete.url({ id:file.image_id }) )
                maxFilesNumber.value++;

            }

            return this; // Dropzone expects the instance returned
        },

        init: function (this: Dropzone) {
            // Load existing images
            props.item?.images?.forEach((image) => {
                const mockFile = {
                    name: image.img.name,
                    size: image.img.size,
                    image_id: image.img.id,
                    accepted: true,
                } as any;



                this.displayExistingFile(mockFile, image.img.original_url);

                // Remove progress bar for existing files
                if (mockFile.previewElement) {
                    mockFile.previewElement
                        .querySelector('.dz-progress')
                        ?.remove();
                }
            });

            // Click to view full image logic (Fixed nesting)
            this.on('addedfile', (file: any) => {
                file.previewElement.addEventListener('click', () => {
                    const img = file.previewElement.querySelector('img');

                    if (img?.src) {
                        window.open(img.src, '_blank');
                    }
                });
            });
        },
    });

    dzInstance.on('sending', (file: any, xhr: any, formData: any) => {
        formData.append('modelType', props.modelType);
        formData.append('modelId', props.modelId?.toString());
        formData.append('collection', props.collection);
    });

    dzInstance.on('success', (file: any, response: any) => {
        file.image_id = response.id;
        maxFilesNumber.value--;
    });
});

// Update dropzone options when the ref changes
watch(maxFilesNumber, (newVal) => {
    if (dzInstance) {
        dzInstance.options.maxFiles = newVal;
    }
});
</script>

<template>
    <div class="flex flex-col items-center">
        <div class="flex items-center justify-center">
            <div
                class="rounded-full border border-gray-200/20 px-3 py-1 text-sm text-yellow-200"
            >
                {{ $t('general.You can only upload') }}
                <span
                    class="mx-1 rounded-full border border-gray-200/40 bg-zinc-900 px-2 text-sm text-white"
                >
                    {{ maxFilesNumber }}
                </span>
                <span>
                    {{
                        maxFilesNumber === 1
                            ? $t('general.image')
                            : $t('general.images')
                    }}
                </span>
            </div>
        </div>

        <div
            class="dropzone mt-3 w-full rounded-lg border-2 border-dashed border-gray-300 bg-white shadow-xl"
            id="image-upload"
        >
            <div class="dz-message" data-dz-message>
                <div class="text-gray-400">
                    {{ $t('general.press here or drage and drop images here') }}
                </div>
            </div>
        </div>
    </div>
</template>

<style>
/* Adjust error message position to not block delete link */
.dropzone .dz-preview .dz-error-message {
    top: 150px !important;
}

/* Ensure the dropzone has a minimum height */
#image-upload {
    min-height: 200px;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
}
</style>
