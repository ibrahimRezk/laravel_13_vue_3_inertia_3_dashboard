<script setup lang="ts">
import { computed } from "vue";

// 1. Define the allowed icon names to get full IDE autocompletion
type IconName = 
    | "Dashboard" | "Profile" | "System Admins & Permissions" | "Users" 
    | "Parents" | "Teachers" | "Personnel Affairs" | "Stages" 
    | "Production Lines Operations" | "Stores Management" | "Inventory Operations"
    | "Annual Leave Balances" | "Sales" | "Employees Terminations" | "Stages_list"
    | "Grades" | "Classrooms_list" | "Classrooms" | "System Event Logs" 
    | "Attendance" | "AcademicYears" | "Attendances" | "Attendance_reports"
    | "Quizzes_reports" | "Quizzes" | "Library" | "OnlineClasses" | "Settings"
    | "General Settings" | "Students_list" | "Students_promotions" 
    | "Students_graduations" | "Doctors" | "Patients" | "Radiologists" 
    | "Lab_Specialists" | "Fees" | "Ambulances" | "Insurance_Companies"
    | "Single_Services" | "Salaries" | "Invoices" | "Remove_fees" 
    | "Group_Invoices" | "Accounts" | "Settlements" | "Receipts" 
    | "Payments" | "Doctor_diagnoses" | "diagnoses_List" | "completed_diagnoses_List"
    | "Review_diagnoses_List" | "Radiologies_list" | "Laboratories_list" 
    | "Patient_List" | "Invoices_List" | "Laboratory" | "Lab_results" 
    | "Radiology" | "Radio_results" | "Investigations" | "Services"
    | "Treasury Shift Activity" | "Single_Invoice" | "Roles and Permissions";

// 2. Define Props using TypeScript
interface Props {
    icon: IconName | string;
    active?: boolean;
    small?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    icon: "",
    active: false,
    small: false
});

// 3. Typed Icon Mappings
const itemIcon_heroicon = computed((): string | undefined => {
    const icons: Record<string, string> = {
        Dashboard: "M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z",
        Profile: "M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z",
        "System Admins & Permissions": "M12.516 2.17a.75.75 0 00-1.032 0 11.209 11.209 0 01-7.877 3.08.75.75 0 00-.722.515A12.74 12.74 0 002.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 00.374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 00-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08zm3.094 8.016a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z",
        // ... (Collapse other paths for brevity, keep your existing strings here)
        Salaries: "M2.25 4.125c0-1.036.84-1.875 1.875-1.875h5.25c1.036 0 1.875.84 1.875 1.875V17.25a4.5 4.5 0 11-9 0V4.125zm4.5 14.25a1.125 1.125 0 100-2.25 1.125 1.125 0 000 2.25z",
    };
    return icons[props.icon];
});

const itemIcon_heroicon2 = computed((): string | undefined => {
    const icons: Record<string, string> = {
        Dashboard: "M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z",
        // ... (Include your heroicon2 paths here)
    };
    return icons[props.icon];
});

const itemIcon_heroicon3 = computed((): string | undefined => {
    const icons: Record<string, string> = {
        "Treasury Shift Activity": "M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z",
        // ... (Include your heroicon3 paths here)
    };
    return icons[props.icon];
});



///// check this part   it it needed /////////////////////////////////////////////////////////////

const colorClasses = computed(() => {
    if (props.active) {
        return "bg-transparent  ";
        // return "bg-linear-to-r  ";
    }else{
        return ''
    }
});




const iconClasses = computed(() => {
    // return "text-white  ";
    return props.active ? "text-zinc-800" : "text-zinc-200";
});

const classes = `flex justify-center ltr:mr-1  rtl:ml-1 shadow-soft-2xl  flex  items-center justify-center    bg-center stroke-0 text-center p-0.5  ${ colorClasses.value }  focus:border-white/20 focus:shadow-outline-yellow text-white font-normal  shadow-md  text-shadow-none hover:scale-110

${
    props.small == true
        ? "w-5 h-5 rtl:mr-2 ltr:ml-2 bg-linear-to-l  hover:from-gray-900 hover:to-zinc-500 active:bg-yellow-900 from-black to-zinc-600 rounded-full border border-gray-500/80"
        : "w-8 h-8  rounded-md border border-white/10 bg-transparent"
}  
`;

///////////////////////// end of part         /////////////////////////////////////////////
</script>

<template>
    <div :class="classes" :title="`${$t('mainMenu.' + props.icon)}`">
        <svg
            v-if="props.small == false"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
            class="w-6 h-5"
            className="h-63 w-63"
            :class="iconClasses"
        >
            <path :d="itemIcon_heroicon" />
            <path :d="itemIcon_heroicon2" />
            <path :d="itemIcon_heroicon3" />
        </svg>
        <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
            class="w-6 h-5"
            className="h-63 w-63"
        ></svg>
    </div>
    <!-- <div  :class="classes">
                  <svg class="h-5 w-5" viewBox="0 0 45 40" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <title>shop</title>
                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                      <g transform="translate(-1716.000000, -439.000000)" fill="#FFFFFF" fill-rule="nonzero">
                        <g transform="translate(1716.000000, 291.000000)">
                          <g transform="translate(0.000000, 148.000000)">
                            <path
                              :class="iconClasses"
                              :d="itemIcon"
                            ></path>
                            <path
                              class="opacity-60  "
                              :class="iconClasses"
                              :d="itemIcon2"
                            ></path>
                            
                          </g>
                        </g>
                      </g>
                    </g>
                  </svg>
                </div> -->

    <slot />
</template>
