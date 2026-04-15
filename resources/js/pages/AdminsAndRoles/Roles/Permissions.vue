<script setup lang="ts">
import { computed, ref } from "vue";
import Table from "@/components/Table/Table.vue";
import Td from "@/components/Table/Td.vue";
import {  attachPermission , detachPermission  } from '@/routes/roles'

import { trans } from "laravel-vue-i18n";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";



interface permission {
    id: number;
    name:string ;
    permissions: string[]

}
interface rolePermission {
    id: number;
    name: string
}


interface role {
    id?: number;
    name?: string;
    slug: {
        ar: string;
        en: string;
    };
    permissions: rolePermission[]
}


interface Props {
    item?: role; 
    pagesPermissions?: permission[]  

}


const props = withDefaults(defineProps<Props>(), {

    item: () => ({
       id: 0,
       name: '',
        slug: {
        ar: '',
        en: ''
    },
    permissions : []
    }),
        pagesPermissions: () => ([]),

});




// const props = defineProps({
//     role: {
//         type: Object,
//         default: () => {
//             permissions: [];
//         },
//     },
//     // permissions: {
//     //     type: Array,
//     // },
//     pagesPermissions: {
//         type: Array,
//     },
//     // specialPermissions: {
//     //     type: Array,
//     // },
//     breadcrumbs: {
//         type: [Array, Object],
//         default: [{}],
//     },
// });

const roleHasPermission = (permission: number) => {
    /// return boolean
    return props.item.permissions.some((p) => p.id == permission);
};
const direction = ref(
    document.getElementsByTagName("html")[0].getAttribute("dir")
);

// const attachDeattachPermission = (event, permission) => {
//     if (event.target.checked == true) {
//         axios
//             .post(route("roles.attach-permission"), {
//                 roleId: props.role.id,
//                 permissionId: permission,
//                 type: 1 // normal permission to be assigned to a role
//             })
//             .then((response) => {
//                 return toastMethod(event, response.data);
//             });
//     } else if (event.target.checked == false) {
//         axios
//             .post(route("roles.detach-permission"), {
//                 roleId: props.role.id,
//                 permissionId: permission,
//                 type: 1 // normal permission to be assigned to a role

//             })
//             .then((response) => {
//                 return toastMethod(event, response.data);
//             });
//     }
// };

interface ApiResponse {
    result: string;
    message: string;
}

const attachDeattachPermission = (event: Event, permission: number | string): void => {
    // Cast the target to HTMLInputElement to access .checked
    const target = event.target as HTMLInputElement;
    const isChecked = target.checked;

    // Determine the route based on checkbox state
    const url = isChecked 
        ? attachPermission().url
        : detachPermission().url;

    axios
        .post<ApiResponse>(url, {
            roleId: props.item.id,
            permissionId: permission,
            type: 1 
        })
        .then((response) => {
            toastMethod(event, response.data);
        })
        .catch((error) => {
            console.log(error)
            // Optional: Handle network errors by reverting the UI state
            target.checked = !isChecked;
        });
};

const toastMethod = (event: Event, response: ApiResponse): void => {
    const target = event.target as HTMLInputElement;

    if (response.result !== "error") {
        toast(trans(`general.${response.message}`), {
            type: toast.TYPE.SUCCESS,
            autoClose: 2500,
            theme: "colored",
            position: direction.value === "ltr"
                ? toast.POSITION.TOP_RIGHT
                : toast.POSITION.TOP_LEFT,
            rtl: direction.value !== "ltr",
            transition: "bounce",
            hideProgressBar: false,
            pauseOnHover: true,
        });
    } else {
        // Revert the checkbox state if the backend returned an error
        target.checked = !target.checked;
        
        toast(trans(`general.something goes wrong`), {
            type: toast.TYPE.ERROR,
            autoClose: 2500,
            theme: "colored",
            position: direction.value === "ltr"
                ? toast.POSITION.TOP_RIGHT
                : toast.POSITION.TOP_LEFT,
            rtl: direction.value !== "ltr",
            transition: "bounce",
            hideProgressBar: false,
            pauseOnHover: true,
        });
    }
};

// const toastMethod = (event, response) => {
//     if (response.result !== "error") {
//         toast(trans(`general.${response.message}`), {
//             type: toast.TYPE.SUCCESS,
//             autoClose: 2500,
//             theme: "colored",
//             position:
//                 direction.value == "ltr"
//                     ? toast.POSITION.TOP_RIGHT
//                     : toast.POSITION.TOP_LEFT,
//             rtl: direction.value == "ltr" ? false : true,
//             transition: "bounce", // flip , slide , zoom , bounce
//             hideProgressBar: false,
//             pauseOnHover: true,
//         });
//     } else {
//         event.target.checked = !event.target.checked; // this line to undo check or uncheck action to return it to it's original case becase we have here error
//         toast(trans(`general.something goes wrong`), {
//             ////////////////////
//             type: toast.TYPE.ERROR, /////////////////////
//             autoClose: 2500,
//             theme: "colored",
//             position:
//                 direction.value == "ltr"
//                     ? toast.POSITION.TOP_RIGHT
//                     : toast.POSITION.TOP_LEFT,
//             rtl: direction.value == "ltr" ? false : true,
//             transition: "bounce", // flip , slide , zoom , bounce
//             hideProgressBar: false,
//             pauseOnHover: true,
//         });
//     }
// };

const headers = [
    { name: "#", label: "#" },
    { name: "name", label: "page name" },
    {
        name: "view",
        label: "view",
    },
    {
        name: "create",
        label: "create",
    },
    {
        name: "edit",
        label: "edit",
    },
    {
        name: "delete",
        label: "delete",
    },
    {
        name: "approve",
        label: "approve",
    },
    {
        name: "close",
        label: "close",
    },
    {
        name: "cancel",
        label: "cancel",
    },
    {
        name: "print",
        label: "print",
    },
];
// const specialPermissionsheaders = [
//     { name: "#", label: "#" },
//     { name: "name", label: "permission name" },
   
//     {
//         name: "select",
//         label: "select",
//     },
// ];


const checkboxClasses = computed(() => {
    return "rounded text-yellow-600 border-gray-300 shadow-sm focus:ring-indigo-500";
});
// const animate = ref(true);
// const startLeaveAnimation = () => {
//     animate.value = false;
// };
</script>

<template>
    <!-- <hr class="h-px bg-white bg-gradient-horizontal-dark mt-2" /> -->


    <Table
    :headers="headers"
    :items="props.pagesPermissions"
    noCheckAll
    tableHeight="customtableheight"
            noPagination
            noNamePadding
            class="mt-2 "
            bodyClasses=" rounded-null"
        >
            <template #title>
                {{ $t("general.permissions") }}
            </template>
            <template v-slot="{ item, index }">
                <!-- //////////////////////////checked row item///////////////////////// -->
    
                <!-- ///////////////////////////////////////////////////// -->
                <Td light>
                    {{ index + 1 }}
                </Td>
    
                <Td light>

                    <div class="flex min-w-36">
                        {{ item.name }}
                    </div>
                </Td>
    
               <Td light>
                    <input
                        @change="
                            attachDeattachPermission(
                                $event,
                                item.permissions['view']
                            )
                        "
                        :class="checkboxClasses"
                        type="checkbox"
                        v-show="item.permissions['view']"
                        :disabled="item.permissions['view'] == null"
                        :checked="roleHasPermission(item.permissions['view'])"
                    />
                </Td>
                 <Td light>
                    <input
                        @change="
                            attachDeattachPermission(
                                $event,
                                item.permissions['create']
                            )
                        "
                        :class="checkboxClasses"
                        type="checkbox"
                        v-show="item.permissions['create']"
                        :disabled="item.permissions['create'] == null"
                        :checked="roleHasPermission(item.permissions['create'])"
                    />
                </Td>
                <Td light>
                    <input
                        @change="
                            attachDeattachPermission(
                                $event,
                                item.permissions['edit']
                            )
                        "
                        :class="checkboxClasses"
                        type="checkbox"
                        v-show="item.permissions['edit']"
                        :disabled="item.permissions['edit'] == null"
                        :checked="roleHasPermission(item.permissions['edit'])"
                    />
                </Td>
                <Td light>
                    <input
                        @change="
                            attachDeattachPermission(
                                $event,
                                item.permissions['delete']
                            )
                        "
                        :class="checkboxClasses"
                        type="checkbox"
                        v-show="item.permissions['delete']"
                        :disabled="item.permissions['delete'] == null"
                        :checked="roleHasPermission(item.permissions['delete'])"
                    />
                </Td>
                <Td light>
                    <input
                        @change="
                            attachDeattachPermission(
                                $event,
                                item.permissions['approve']
                            )
                        "
                        :class="checkboxClasses"
                        type="checkbox"
                        v-show="item.permissions['approve']"
                        :disabled="item.permissions['approve'] == null"
                        :checked="roleHasPermission(item.permissions['approve'])"
                    />
                </Td>
                <Td light>
                    <input
                        @change="
                            attachDeattachPermission(
                                $event,
                                item.permissions['close']
                            )
                        "
                        :class="checkboxClasses"
                        type="checkbox"
                        v-show="item.permissions['close']"
                        :disabled="item.permissions['close'] == null"
                        :checked="roleHasPermission(item.permissions['close'])"
                    />
                </Td>
                <Td light>
                    <input
                        @change="
                            attachDeattachPermission(
                                $event,
                                item.permissions['cancel']
                            )
                        "
                        :class="checkboxClasses"
                        type="checkbox"
                        v-show="item.permissions['cancel']"
                        :disabled="item.permissions['cancel'] == null"
                        :checked="roleHasPermission(item.permissions['cancel'])"
                    />
                </Td>
                <Td light>
                    <input
                        @change="
                            attachDeattachPermission(
                                $event,
                                item.permissions['print']
                            )
                        "
                        :class="checkboxClasses"
                        type="checkbox"
                        v-show="item.permissions['print']"
                        :disabled="item.permissions['print'] == null"
                        :checked="roleHasPermission(item.permissions['print'])"
                    />
                </Td>
            </template>
        </Table>


</template>
