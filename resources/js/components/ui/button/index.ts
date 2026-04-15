import { cva, type VariantProps } from 'class-variance-authority'

export { default as Button } from './Button.vue'

export const buttonVariants = cva(
  'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*=\'size-\'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
  {
    variants: {
      variant: {
        default:
          'bg-primary text-primary-foreground shadow-xs hover:bg-primary/90',
        destructive:
          'bg-destructive text-white shadow-xs hover:bg-destructive/90 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40 dark:bg-destructive/60',
        outline: 
          'border bg-background shadow-xs hover:bg-accent hover:text-accent-foreground dark:bg-input/30 dark:border-input dark:hover:bg-input/50',
        secondary:
          'bg-secondary text-secondary-foreground shadow-xs hover:bg-secondary/80',
        ghost:
          'hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50',
        link: 'text-primary underline-offset-4 hover:underline',






          lime:"text-zinc-900 bg-linear-to-r from-lime-200 via-lime-400 to-lime-500 hover:bg-linear-to-br focus:ring-4 focus:outline-none focus:ring-lime-300 dark:focus:ring-lime-800 shadow-lg shadow-lime-500/50 dark:shadow-lg dark:shadow-lime-800/80 font-medium rounded-lg text-sm px-10 py-2.5 text-center me-2 mb-2 transition duration-300 ease-in-out transform hover:scale-105" ,
        cyan:"text-white bg-linear-to-r from-cyan-400 via-cyan-500 to-cyan-600 hover:bg-linear-to-br focus:ring-4 focus:outline-none focus:ring-cyan-300 dark:focus:ring-cyan-800 shadow-lg shadow-cyan-500/50 dark:shadow-lg dark:shadow-cyan-800/80 font-medium rounded-lg text-sm px-10 py-2.5 text-center me-2 mb-2 transition duration-300 ease-in-out transform hover:scale-105",
        violet:"bg-linear-to-r from-blue-500 to-purple-500 text-white py-2 px-4 rounded transition duration-300 ease-in-out transform hover:scale-105",
        roundedBlue:"bg-blue-500 hover:bg-blue-700 mt-5 text-white  py-2 px-4 rounded-full border border-blue-500 shadow-md transition duration-300 ease-in-out transform hover:scale-105",
        colorized: "bg-linear-to-r from-[#F10E17] via-[#F1770E] to-[#F10E88]  text-white  rounded-xl border border-zinc-200/40 shadow-md transition duration-300 ease-in-out transform hover:scale-105 text-sm ", 
        
        
        black: "font-normal bg-zinc-800 hover:bg-zinc-900 active:bg-zinc-900 focus:border-white/50 focus:shadow-outline-zinc text-white  shadow-md border border-zinc-500 text-shadow-none ",   
        linear_black: "bg-linear-to-l font-normal  from-black to-zinc-800 hover:from-black hover:to-zinc-900 active:bg-zinc-900 focus:border-white/50 focus:shadow-outline-zinc text-white  shadow-md border border-zinc-500 text-shadow-none    ",
        linear_black_normal: " bg-linear-to-l font-normal  from-black to-zinc-800 hover:from-black hover:to-zinc-900 active:bg-zinc-900 focus:border-white/50 focus:shadow-outline-zinc text-white  shadow-md border border-zinc-500 text-shadow-none   ",

        red: "font-normal bg-red-600 hover:bg-red-700 active:bg-red-700 focus:border-white/50 focus:shadow-outline-white text-white border border-red-100 ",
        linear_red: "font-normal bg-linear-to-l from-red-700 to-red-800 hover:from-red-800 hover:to-red-900 active:bg-red-700/20 focus:border-red-400 focus:shadow-outline-white text-white border border-red-500  ",
        orange: "font-normal  bg-orange-600 hover:bg-orange-900 active:bg-orange-900 focus:border-white/50 focus:shadow-outline-orange text-white  shadow-md border border-orange-300 text-shadow-none ",
        linear_orange: `font-normal bg-linear-to-l from-orange-900  to-orange-400 hover:from-zinc-900 hover:to-orange-500 active:bg-orange-900 focus:border-white/50 focus:shadow-outline-orange text-white  shadow-md border border-orange-400 text-shadow-none   `,
        linear_orange_normal: `font-normal bg-linear-to-l from-orange-800  to-orange-400 hover:from-zinc-900 hover:to-orange-500 active:bg-orange-900 focus:border-white/50 focus:shadow-outline-orange text-white  shadow-md border border-orange-300 text-shadow-none   $ `,
        green: "font-normal bg-lime-600 hover:bg-lime-700 active:bg-lime-700 focus:border-lime-700 focus:shadow-outline-lime text-white shadow-md border border-lime-300 ",
        linear_green: "font-normal bg-linear-to-l from-lime-900 to-lime-600 hover:from-lime-600 hover:to-lime-400 active:bg-lime-700 focus:border-lime-700 focus:shadow-outline-lime text-white shadow-md border border-lime-600   ",
        blue: "bg-sky-600 hover:bg-sky-700 active:bg-sky-700 focus:bg-sky-700 focus:border-blue-700 focus:shadow-outline-blue text-white  shadow-xl border border-lime-400 ",
        linear_blue: " font-normal bg-linear-to-l from-blue-800 to-blue-500 hover:from-blue-600 hover:to-blue-400 active:bg-blue-700 focus:bg-blue-700 focus:border-blue-700 focus:shadow-outline-blue text-white  shadow-xl border border-blue-400  ",
        white: "bg-zinc-200 hover:bg-zinc-100 active:bg-zinc-50 focus:border-zinc-300 focus:shadow-outline-zinc text-black  font-bold drop-shadow-md border ",
        linear_white: "bg-linear-to-l  from-zinc-400 to-zinc-200 hover:from-zinc-500 hover:to-zinc-300 active:bg-zinc-50 focus:border-zinc-300 focus:shadow-outline-zinc text-black border border-zinc-500  font-bold drop-shadow-md border border-black  ",
        linear_white_normal: "bg-linear-to-l from-zinc-400 to-zinc-200 hover:from-zinc-500 hover:to-zinc-300 active:bg-zinc-50 focus:border-zinc-300 focus:shadow-outline-zinc text-black border border-zinc-500  font-bold drop-shadow-md border border-black ",
        yellow: "bg-yellow-100 hover:bg-yellow-50 active:bg-red-500 focus:border-zinc-200 focus:shadow-outline-black text-black font-bold border border-zinc-300  border border-yellow-700  font-normal",
        linear_yellow: " bg-linear-to-l from-yellow-800 to-yellow-500 hover:from-zinc-900 hover:to-yellow-500 active:bg-yellow-900 focus:border-white/50 focus:shadow-outline-yellow text-white font-normal  shadow-md border border-yellow-600 text-shadow-none  ",
        linear_yellow_normal: " bg-linear-to-l from-yellow-800 to-yellow-500 hover:from-zinc-900 hover:to-yellow-500 active:bg-yellow-900 focus:border-white/50 focus:shadow-outline-yellow text-white font-normal  shadow-md border border-yellow-300 text-shadow-none  ",
        
        transparent_white: "bg-linear-to-l  from-transparent via-zinc-200/50 to-transparent hover:from-zinc-500 hover:to-zinc-300 active:bg-zinc-50 focus:border-zinc-300 focus:shadow-outline-zinc text-black dark:text-zinc-200 dark:font-normal dark:via-zinc-800 border border-zinc-200/20  font-bold drop-shadow-md   ",

        transparent_yellow: " align-middle  rounded-lg shadow-sm ring-1 transition duration-75  focus-within:ring-20 focus-within:ring-orange-300/20 ring-yellow-500/30  border-none  text-base text-yellow-500  placeholder:text-zinc-400 focus:ring-1   xs:text-xs sm:leading-6    bg-zinc-900/80 transpare    focus:border-yellow-400  focus:ring-yellow-200/40 focus:ring-opacity-60 ",
        transparent_red: " align-middle  rounded-lg shadow-sm ring-1 transition duration-75  focus-within:ring-20 focus-within:ring-orange-300/20 ring-red-500/30  border-none  text-base text-red-400  placeholder:text-zinc-400 focus:ring-1   xs:text-xs sm:leading-6    bg-zinc-900 transpare    focus:border-red-400  focus:ring-red-200/40 focus:ring-opacity-60 ",
        transparent_orange: " align-middle  rounded-lg shadow-sm ring-1 transition duration-75  focus-within:ring-20 focus-within:ring-orange-300/20 ring-orange-500/30  border-none  text-base text-orange-300  placeholder:text-zinc-400 focus:ring-1   xs:text-xs sm:leading-6    bg-zinc-900/80 transpare    focus:border-orange-400  focus:ring-orange-200/40 focus:ring-opacity-60 ",
        transparent_green: " align-middle  rounded-lg shadow-sm ring-1 transition duration-75  focus-within:ring-20 focus-within:ring-orange-300/20 ring-lime-500/30  border-none  text-base text-lime-400  placeholder:text-zinc-400 focus:ring-1   xs:text-xs sm:leading-6    bg-zinc-900/80 transpare    focus:border-lime-400  focus:ring-lime-200/40 focus:ring-opacity-60 ",
        transparent_blue: " align-middle  rounded-lg shadow-sm ring-1 transition duration-75  focus-within:ring-20 focus-within:ring-orange-300/20 ring-blue-500/30  border-none  text-base text-blue-400  placeholder:text-zinc-400 focus:ring-1   xs:text-xs sm:leading-6    bg-zinc-900/80 transpare    focus:border-blue-400  focus:ring-blue-200/40 focus:ring-opacity-60 ",

        transparent_black: "flex items-center align-middle  rounded-lg shadow-sm ring-1 transition duration-75  focus-within:ring-20 focus-within:ring-orange-300/20 ring-orange-500/30  border-none  text-base text-zinc-400  placeholder:text-zinc-400 focus:ring-1   xs:text-xs     bg-zinc-900/80 transpare     focus:border-orange-400  focus:ring-orange-200/40 focus:ring-opacity-60 ",

      },
      size: { 
        default: 'h-9 px-4 py-2 capitalize has-[>svg]:px-3',
        xs: 'h-5 rounded-sm capitalize text-xs  gap-1.5 px-2 has-[>svg]:px-2.5',
        sm: 'h-5 rounded-md  capitalize gap-1.5 text-[12px] px-3 has-[>svg]:px-2.5',
        md: 'h-6 rounded-md text-[13px] capitalize gap-1.5 px-3 has-[>svg]:px-3',
        lg: 'h-10 rounded-md capitalize px-6 has-[>svg]:px-4',
        icon: 'size-9',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
    },
  },
)

export type ButtonVariants = VariantProps<typeof buttonVariants>
