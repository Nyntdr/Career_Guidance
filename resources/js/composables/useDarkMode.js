import { ref, watch } from 'vue';

const isDark = ref(false);

const STORAGE_KEY = 'career-guide-theme';

function applyDark(value) {
    isDark.value = value;
    document.documentElement.classList.toggle('dark', value);
}

function initTheme() {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored !== null) {
        applyDark(stored === 'dark');
    } else {
        applyDark(window.matchMedia('(prefers-color-scheme: dark)').matches);
    }
}

function toggleDark() {
    applyDark(!isDark.value);
    localStorage.setItem(STORAGE_KEY, isDark.value ? 'dark' : 'light');
}

initTheme();

watch(isDark, (val) => {
    document.documentElement.classList.toggle('dark', val);
});

export function useDarkMode() {
    return { isDark, toggleDark };
}
