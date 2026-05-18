import { ref } from 'vue'

const header = ref(null)
const subHeader = ref(null)

export function usePageHeader() {
    return { header , subHeader }
}