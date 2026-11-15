import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

export function usePublicTheme() {
    const theme = ref('system');
    const isDark = ref(false);
    let systemTheme;

    function applyTheme() {
        isDark.value = theme.value === 'dark' || (theme.value === 'system' && systemTheme?.matches);
        document.documentElement.dataset.theme = isDark.value ? 'dark' : 'light';
    }

    onMounted(() => {
        systemTheme = window.matchMedia('(prefers-color-scheme: dark)');
        try { theme.value = window.localStorage.getItem('profile-theme') ?? 'system'; } catch { /* Use the system theme if storage is unavailable. */ }
        systemTheme.addEventListener('change', applyTheme);
        applyTheme();
    });

    watch(theme, (value) => {
        try { window.localStorage.setItem('profile-theme', value); } catch { /* Theme selection still works without storage. */ }
        applyTheme();
    });

    onBeforeUnmount(() => systemTheme?.removeEventListener('change', applyTheme));

    return { theme, isDark };
}

// Native dialogs keep focus inside the viewer and make the page behind it inert.
export function useDetailsDialog() {
    const selectedItem = ref(null);
    const dialogRef = ref(null);
    let trigger;
    let previousOverflow;

    function openDetails(item, event) {
        trigger = event?.currentTarget ?? document.activeElement;
        selectedItem.value = item;
    }

    function closeDetails() {
        dialogRef.value?.close();
        selectedItem.value = null;
        if (previousOverflow !== undefined) {
            document.body.style.overflow = previousOverflow;
            previousOverflow = undefined;
        }
        if (trigger?.isConnected) trigger.focus({ preventScroll: true });
        trigger = null;
    }

    watch(selectedItem, async (item) => {
        if (!item) return;
        await nextTick();
        if (!selectedItem.value || !dialogRef.value) return;
        if (previousOverflow === undefined) previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        if (!dialogRef.value.open) dialogRef.value.showModal();
    });

    onBeforeUnmount(closeDetails);

    return { selectedItem, dialogRef, openDetails, closeDetails };
}
