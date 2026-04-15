<script setup lang="ts">
import Label from "@/components/Label.vue";
import InputError from "@/components/InputError.vue";


// The reason your event was running twice in the commented-out code is Attribute Inheritance. By default, Vue applies listeners (like @change or @click) to the root element. When you wrap a custom component inside a <Label>, and both have listeners or v-model bindings, the event bubbles up or triggers twice.

// Here is the fixed, clean version. I have used inheritAttrs: false to ensure that any extra attributes (like id or extra event listeners) go specifically to the <input> and not the wrapper <div>.


// Use defineOptions to prevent attributes from leaking to the root <div>
defineOptions({
    inheritAttrs: false
});

interface Props {
    disabled?: boolean;
    label?: string;
    errorMessage?: string;
    value?: string | number | object | null;
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
    label: "",
    errorMessage: "",
    value: null,
});

const checked = defineModel<any>('checked');
</script> 

<template>
    <div class="flex flex-col">
        <Label class="flex items-center cursor-pointer">
            <input
                type="checkbox"
                v-model="checked"
                :value="props.value"
                :disabled="props.disabled"
                v-bind="$attrs"
                class="rounded my-2 text-yellow-600 dark:text-slate-400/10 dark:border-gray-300/50 checked:border-gray-100/50 border dark:bg-black/40 border-gray-300 shadow-sm focus:ring-indigo-500 checked:hover:border-gray-200 disabled:opacity-50"
            >
            <span v-if="label" class="mx-3 text-sm text-zinc-700 dark:text-zinc-300">
                {{ label }}
            </span>
        </Label>

        <InputError 
            v-if="errorMessage"
            class="mt-1"
            :message="errorMessage" 
        />
    </div>
</template>