// import { defineStore } from 'pinia'

// export const useGeneralStore = defineStore('general', {
//     state: () => ({
//         animate: false,
//         paginationNumber: 10,
//     }), 
//     persist: true,
// })


import { defineStore } from 'pinia'

// 1. Define the shape of your state
interface GeneralState {
  animate: boolean
  paginationNumber: number
}

export const useGeneralStore = defineStore('general', {
  // 2. Add the return type annotation to the state function
  state: (): GeneralState => ({ 
    animate: false,
    paginationNumber: 10,
  }),
  persist: true,
})