<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    opened : {
        type : Boolean, 
        default : false
    } ,
    align: {
        type: String,
        default: 'right',
    },
    width: {
        type: String,
        default: '48',
    },
    keepOpened: {
        type: Boolean,
        default: false,
    },

    contentClasses: {
        type:[ Array , String],
        default: () => ['py-1', 'bg-zinc-200 dark:bg-background'],
    },
});

const open = ref(false);
const screenEffect = ref(false); // to allow clicking outside dropdown menu to close it

const closeOnEscape = (e: any) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const widthClass = computed(() => {
    return {
        '32': 'w-32',
        '36': 'w-36',
        '40': 'w-40',
        '44': 'w-44',
        '48': 'w-48',
        '52': 'w-52',
        '56': 'w-56',
        '60': 'w-60',
        '64': 'w-64',
        '72': 'w-72',
        '80': 'w-80',
        '96': 'w-96',
    }[props.width.toString()];
});

const alignmentClasses = computed(() => {
    if (props.align === 'right') {
        return 'origin-top-left left-0';
    }

    if (props.align === 'left') {
        return 'origin-top-right right-0';
    }

    return 'origin-top';
});

// const alignmentClasses = computed(() => {
//     if (props.align === 'left') {
//         return 'ltr:origin-top-left rtl:origin-top-right start-0';
//     }

//     if (props.align === 'right') {
//         return 'ltr:origin-top-right rtl:origin-top-left end-0';
//     }

//     return 'origin-top';
// });
// const check = ()=>{console.log('check')}


// onMounted(()=> {
//     document.addEventListener('mousemove', function (event) {
//   let directionX = event.movementX || event.mozMovementX || event.webkitMovementX || 0;
//   let directionY = event.movementY || event.mozMovementY || event.webkitMovementY || 0;

//   console.log(directionX)
//   console.log(directionY)
// });
// })

const closeDropdownAndScreenEffect = ()=>{
    open.value =  props.keepOpened
    screenEffect.value = false
}

</script>

<template>
    <div class="relative" 
    @mouseleave="closeDropdownAndScreenEffect"  
    @mouseenter="open = true" >
    <!-- @mouseenter="open = true" @click="open = !open" > -->

       
            <div 
           
          >
                <slot name="trigger" />
            </div>
    
            <!-- this will make problem if used with mouseenter and mouseleave events -->
            <!-- Full Screen Dropdown Overlay --> 
            <!-- <div v-show="open" class="fixed inset-0 z-40" @click="open = false" /> -->
    
            <transition 
                enter-active-class="transition ease-out duration-200"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
            >
                <div
         
                    v-show="open"
                    class="absolute z-50 pt-2 rounded-md shadow-lg  py-"
                    :class="[widthClass, alignmentClasses]"
                    style="display: none;"
                    @click="open = true"
                >
                    <div  @mouseenter="screenEffect = true"   class="rounded-md ring-1 min-w-36 ring-black/20 ring-opacity-5" :class="contentClasses">
                        <slot name="content" />
                    </div>
                </div>
            </transition>
        </div>

                <div  v-show="screenEffect == true" class="fixed inset-0" @click="closeDropdownAndScreenEffect"/>

        <!-- <div v-show="show" class="fixed inset-0 transform transition-all" @click="close">
                    <div class="absolute inset-0 bg-gray-500 opacity-75 " />
                </div> -->

</template>
