import { ref } from 'vue';

export function useHoverDropdown() {
    const isOpen = ref(false);
    let timer: ReturnType<typeof setTimeout> | null = null;

    // Function to open the menu and cancel any pending close timer
    const handleEnter = () => {
        if (timer) clearTimeout(timer);
        timer = null;
        isOpen.value = true;
    };

    // Function to start the close timer
    const handleLeave = () => {
        // Delay closing to allow time to cross the gap
        timer = setTimeout(() => {
            isOpen.value = false;
        }, 150);
    };

    return {
        isOpen,
        handleEnter,
        handleLeave,
    };
}
