<script setup lang="ts">
import { toRefs } from "vue";

// Assuming these are standard Vue components that don't need complex typing here
import Button from "@/components/ui/button/Button.vue";
// import FilterIcon from "@/components/Icons/Filter.vue";

// 1. Define an interface for the component props
interface Props {
    show?: boolean; // Optional property with default value
    showDeleteAll?: boolean; // Optional property with default value
    checkedItems?: number; // Optional property with default value
}

// 2. Use the interface with defineProps
// The runtime type declarations are kept for validation
const props = withDefaults(defineProps<Props>(), {
    show: false,
    showDeleteAll: false,
    checkedItems: 0,
});

const {  checkedItems } = toRefs(props);

// 3. Define the emitted events and their payload types
interface Emits {
    (e: "reset"): void;
    (e: "deleteAll"): void;
}
const emit = defineEmits<Emits>();

// Use a ref to control the local filter visibility state, initialized with the prop value
// The type is inferred as boolean | undefined, but since we use withDefaults, it's boolean
// const showFilters = ref(props.show);

// Toggle the filter visibility and emit "reset" if filters are now hidden
// const reset = () => {
//     // Toggle the local state
//     showFilters.value = !showFilters.value; 

//     // Emit 'reset' when the filters are closed (showFilters.value is false)
//     if (showFilters.value === false) {
//         emit("reset");
//     }
// };

// Emit 'deleteAll' event
const deleteAll = () => {
    emit("deleteAll");
};
</script>

<style scoped>
.collapsableDiv {
    overflow: visible;
    transform-origin: top;
}
.collapse-enter-active {
    animation: collapse reverse 300ms ease;
}
.collapse-leave-active {
    animation: collapse 200ms ease;
}
@keyframes collapse {
    from {
        max-height: 300px;
    }
    to {
        max-height: 0px;
    }
}
</style>

<template>
    <div>
        <div class="flex  flex-col  sm:flex-row md:items-center justify-between ">
            <div class="flex flex-col lg:flex-row gap-2 ">
                <div class="flex justify-center sm:justify-between">
                    <slot />
                </div>
                <!-- <Button
                    v-if="$slots.filters"
                    class="rtl:mr-3 ltr:ml-2 inline-flex items-center hover:cursor-pointer"
                    color="linear_black"
                    @click="reset"
                >
                    <FilterIcon class="mx-2" />
                    <span>{{
                        showFilters
                            ? $t("general.reset filter")
                            : $t("general.filter")
                    }}</span>
                </Button> -->

                <div class="flex items-center justify-center">
                    <Button
                        v-if="props.showDeleteAll"
                        class="mx-3 hover:cursor-pointer px-5"
                        v-show="checkedItems > 0"
                        variant="linear_red"
                        size="sm"
                        @click="deleteAll"
                        >{{ $t("general.delete all selected") }}
                    </Button>
                </div>
            </div>
            
            <div class="flex flex-col justify-center sm:justify-between lg:flex-row gap-2">
                <slot name="button" />
            </div>
            
            <Transition name="collapse">
                <div class="collapsableDiv" v-if="props.show">
                    <slot name="filtersExtraButtons" />
                </div>
            </Transition>
            
            <div class="flex justify-center sm:justify-between gap-2">
                <slot name="customHeaderButton" />
            </div>
        </div>
        </div>
</template>