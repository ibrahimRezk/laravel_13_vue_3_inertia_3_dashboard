import { ref, markRaw, type Component } from 'vue'

const isOpen = ref(false)
const view = ref<Component | null>(null)
const props = ref<any>({})

export function useModal() {
  function open(component: Component, payload: any = {}) {
    view.value = markRaw(component)
    props.value = payload
    isOpen.value = true 
  }

  function close() {
    isOpen.value = false
    // Optional: delay clearing view to allow animation to finish
    setTimeout(() => {
      view.value = null
      props.value = {}
    }, 300)
  }

  return { isOpen, view, props, open, close }
}