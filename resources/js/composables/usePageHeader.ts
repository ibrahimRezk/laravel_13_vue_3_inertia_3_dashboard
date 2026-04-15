import { ref } from 'vue'

const header = ref('')
const subHeader = ref('')

export function usePageHeader() {
    return { header , subHeader }
}