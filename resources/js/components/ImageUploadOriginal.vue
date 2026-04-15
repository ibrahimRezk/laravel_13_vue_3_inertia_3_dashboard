<script setup lang="ts">
import { onMounted, ref, watch } from "vue";
import Dropzone from "dropzone";
import { usePage } from "@inertiajs/vue3";
import "dropzone/dist/dropzone.css";
import { trans } from "laravel-vue-i18n";


// import { usePage } from "@inertiajs/vue3";

import "dropzone/dist/dropzone.css";

const props = defineProps({
    item: {
        type: Object,
        default: () => {},
    },
    modelType: {
        type: String,
    },
    collection: {
        type: String,
        default:''
    },
    modelId: {
        type: Number,
        
    },
    maxFilesize: {
        type: Number,
        default: 1024,
    },
    maxFiles: {
        type: Number,
        default: 10,
    },
});


const maxFilesNumber = ref(props.maxFiles);

onMounted(() => {
    Dropzone.autoDiscover = false;
    const dropzone = new Dropzone("#image-upload", {
        url: route("images.store"),
        headers: {
            // "X-CSRF-TOKEN": usePage().props.csrf_token

            "X-CSRF-TOKEN": usePage().props.csrf_token,
            // "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token'] ?.content,") this will not work becase it brings the token from the last session not from currnt one
        },
        paramName: "image",
        maxFilesize: props.maxFilesize,
        // maxFiles: maxFilesNumber.value,  // in watch to be more dynamic
        // acceptedFiles: ".jpeg, .jpg, .png",  / down to accept all types of images
        addRemoveLinks: true,

        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // params: {
        //             _token: '{{ csrf_token() }}'
        //         },
        acceptedFiles: "image/*",
        dictDefaultMessage: trans('general.press here or drage and drop images here'),
        // 'اضغط هنا لرفع الملفات او قم بسحب الملفات  وافلاتها هنا',
        dictFallbackMessage: trans('general.Your browser does not support multi-image and drag and drop') , // emam
        dictInvalidFileType: trans('general.You cannot upload this type of file'), // emam
        dictCancelUpload:trans('general.Cancel upload'), // emam
        dictCancelUploadConfirmation:trans('general.Are you sure you want to cancel the file upload?') , // emam
        dictMaxFilesExceeded:trans('general.You cannot upload more than this number') , // emam

        ///////////// new ///////////////////////////////
        // dictFallbackText:trans('general.Please use the fallback form below to upload your files like in the olden days.'),
        dictFileTooBig:trans('general.File is too big'),
        // dictResponseError: trans('general.Server responded with {{statusCode}} code.'),
        dictRemoveFile:trans('general.delete'),
        ////////////////////////////////////////////////////////////////////

        removedfile: function (file) {
            file.previewElement.remove(); // emam
            axios.delete(route("images.destroy", { id: file.image_id }));
            maxFilesNumber.value = maxFilesNumber.value + 1;
            return;
        },

        init: function () {
            props.item.images?.forEach(
                (image) => {
                    const mock = {
                        name: image.img.name,
                        image_id: image.img.id,
                        size: image.img.size,
                        type: image.img.mime_type,
                    };
                    this.emit("addedfile", mock);

                    // images.img comming from laboratory resourcey
                    this.options.thumbnail.call(this, mock, image.img.original_url);   // this will get the original image 


                    const dzProgress =
                        document.getElementsByClassName("dz-progress");
                    dzProgress[0].classList.remove("dz-progress");
                },

                // this must be inside forEach but must be outside the callback function of foreach    .... basicaly between }, of the callback function  and ) of the foreach;
                this.on("addedfile", function (file) {
                    file.previewElement.addEventListener("click", function () {
                        window.open(
                            file.previewElement.children[0].children[0]
                                .currentSrc,
                            "_blank"
                        );
                    });
                })
            );
        },
    });

    dropzone.on("sending", (file, xhr, formData) => {
        formData.append("modelType", props.modelType);
        formData.append("modelId", props.modelId);
        formData.append("collection", props.collection);
    });
    dropzone.on("success", function (file, response) {
        file.image_id = response.id;
        maxFilesNumber.value = maxFilesNumber.value - 1;
    });

    watch(
        () => maxFilesNumber.value,
        // () => console.log(dropzone.options.maxFiles)
        () => (dropzone.options.maxFiles = maxFilesNumber.value)
    );

    // Jkh6xlVBVhfjYBwt4beUuwUHHmqwszi3WC5LyoO8
});
</script>
<!-- ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////// important code to move the error messages under the images down alittle
bit to show remove link -->
<style>
.dropzone .dz-preview .dz-error-message {
    top: 150px !important;
}
</style>
<!-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->

<template>
    <div class="flex justify-center items-center">
        <div
            class="text-yellow-200 text-sm border border-gray-200/20 rounded-full px-3"
        >
            {{ $t("general.You can only upload") }}
            <span
                class="text-white border rounded-full px-2 mx-1 text-sm border-gray-200/40 bg-zinc-900"
            >
                {{ maxFilesNumber }}</span
            >
            <span>
                {{
                    maxFilesNumber > 1
                        ? $t("general.images")
                        : $t("general.image")
                }}
            </span>
        </div>
    </div>

    <div class="dropzone mt-3 shadow-xl bg-white" id="image-upload">
        <div v-if="props.item.images?.length < 1 ">
        <div class="dz-message" data-dz-message>
            <div>
                    Drop Image<span v-if="maxFilesNumber > 1">s</span> here to upload
            </div>
        </div>
    </div>
    </div>


</template>
