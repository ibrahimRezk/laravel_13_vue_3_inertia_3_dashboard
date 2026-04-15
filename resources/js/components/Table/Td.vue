<script setup lang="ts">
import { computed } from "vue";

// dropshadow here make problem in showing submenu inside a table it shows the submenu nunder other element so we remove it if we have link with submenu inside a table

const props = defineProps({
    NoShadow: {
        type: Boolean,
        default: true,
    },

    light: {
        type: Boolean,
        default: false,
    },
    dark: {
        type: Boolean,
        default: false,
    },
    name: {
        type: String,
        default: "",
    },

});

const viewShadow = computed(() => {
    return props.NoShadow ? "" : "drop-shadow-sm shadow-1  shadow-orange-500";
});

const textColor = computed(() => {
    return props.light ? "text-yellow-50" : "text-gray-500 ";
});

// whitespace-nowrap is going to keep all text in the row in one line  and if the text is too long it still in one line
// const classes = `  capitalize align-middle h-12 bg-transparent border-b  border-gray-500 border-solid shadow-none tracking-none whitespace-nowrap text-sm   ${viewShadow.value} `;
const classes = `  capitalize align-middle h-12 bg-transparent border-b  border-gray-500/40  border-solid shadow-none tracking-none  text-sm  ${viewShadow.value}  `;
</script>

<template>
    <!-- <td
        class="py-1 px-4 capitalize border-b-solid tracking-none whitespace-nowrap text-slate-700 opacity-70"
    > -->

    <td :class="classes">
        <!-- :class="{
            'text-gray-400 cursor-not-allowed': isDisabled(link),
            'text-yellow-300 ': !isDisabled(link),
            'bg-blue-800 font-bold':link.active
        }
            " -->

        <!-- <div class=" "> -->
            <!-- ////////////////////////////////////////// remember here and  add text-center to yes no label  -->
            <div class=" flex"> 

            <div class="rtl:ml-1 ltr:mr-1 flex justify-center align-middle items-center">
                <slot name="image" />
            </div>
            <div class=" w-full ">
                <h6
                    class="mb-0 leading-normal py-1 mx-1  shadow-orange-500 "
                    :class="textColor"
                >
                    <slot />
                </h6>
                <p
                    class="mb-0 leading-tight text-xs text-slate-400 px-2 py-0.5"
                >
                    <slot name="second-line" />
                </p>
            </div>
        </div>
    </td>
</template>
