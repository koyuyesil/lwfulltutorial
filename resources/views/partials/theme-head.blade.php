{{--
    Tema ve menü tercihleri boyamadan ÖNCE uygulanır (titreme olmaz).
    Tema: localStorage "theme" = system | light | dark (varsayılan system → işletim sistemi tercihi).
    Menü: localStorage "sidebar.collapsed" = 1 ise masaüstünde dar menü.
--}}
<script>
    (function () {
        var root = document.documentElement;
        var mq = window.matchMedia('(prefers-color-scheme: dark)');
        window.applyTheme = function (pref) {
            pref = pref || localStorage.getItem('theme') || 'system';
            var dark = pref === 'dark' || (pref === 'system' && mq.matches);
            root.classList.toggle('dark', dark);
            root.style.colorScheme = dark ? 'dark' : 'light';
            root.dataset.theme = pref;
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { dark: dark, pref: pref } }));
        };
        window.applyTheme();
        // Sistem seçiliyse işletim sistemi değişince anında uy
        mq.addEventListener('change', function () { if ((localStorage.getItem('theme') || 'system') === 'system') window.applyTheme(); });
        // wire:navigate sonrası (html özniteliği korunur ama güvence için)
        document.addEventListener('livewire:navigated', function () { window.applyTheme(); });
        root.dataset.collapsed = localStorage.getItem('sidebar.collapsed') === '1' ? 'true' : 'false';
    })();
</script>
