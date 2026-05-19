import { ref } from 'vue'

const header = ref<string | null>(null)
const subHeader = ref<string | null>(null)

export function usePageHeader() {
    return { header , subHeader }
}