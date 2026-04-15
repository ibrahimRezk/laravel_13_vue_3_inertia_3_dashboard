// 1. Rename to PascalCase
export interface FullName {
    // ar: string,
    // en:string
    [key: string]: string;  
}

export function getInitials(fullName?: FullName): string {
    // 3. Safety Check: If no object is passed, return empty immediately
    if (!fullName) return '';

    // 4. Safe DOM Access: Handle SSR (Server Side Rendering) where document is undefined
    let currentLang = 'en';
    if (typeof document !== 'undefined') {
        currentLang = document.documentElement.lang || 'en';
    }

    // 5. Fallback Logic: Try current lang, then fallback to 'en', then fallback to empty string
    const localizedName = fullName[currentLang] || fullName['en'] || '';

    if (!localizedName) return '';

    // 6. Split and Filter: .filter(Boolean) removes empty strings caused by double spaces
    const names = localizedName.trim().split(' ').filter(Boolean);

    if (names.length === 0) return '';
    
    // Single name case
    if (names.length === 1) {
        return names[0].charAt(0).toUpperCase();
    }

    // First and Last name initials
    return `${names[0].charAt(0)}${names[names.length - 1].charAt(0)}`.toUpperCase();
}

// Optional: If you strictly need it as a hook
export function useInitials() {
    return { getInitials };
}