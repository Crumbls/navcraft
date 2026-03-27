@if(class_exists(\Crumbls\Layup\LayupServiceProvider::class))
    @layupScripts
@endif

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('navCraft', (config = {}) => ({
        openMenu: null,
        mobileOpen: false,
        mobileExpanded: null,
        hoverMode: config.hoverMode || 'click',
        hoverTimeout: null,

        toggle(id) {
            this.openMenu = this.openMenu === id ? null : id;
        },

        open(id) {
            this.openMenu = id;
        },

        close(id) {
            if (this.openMenu === id) {
                this.openMenu = null;
            }
        },

        closeAll() {
            this.openMenu = null;
            this.mobileOpen = false;
            this.mobileExpanded = null;
        },

        hoverOpen(id) {
            if (this.hoverMode !== 'hover') return;
            clearTimeout(this.hoverTimeout);
            this.openMenu = id;
        },

        hoverClose(id) {
            if (this.hoverMode !== 'hover') return;
            this.hoverTimeout = setTimeout(() => {
                if (this.openMenu === id) {
                    this.openMenu = null;
                }
            }, 150);
        },

        focusFirst(panelId) {
            this.$nextTick(() => {
                const panel = document.getElementById(panelId);
                const first = panel?.querySelector('a, button');
                if (first) first.focus();
            });
        },

        focusTrigger(triggerId) {
            this.$nextTick(() => {
                const trigger = document.getElementById(triggerId);
                if (trigger) trigger.focus();
            });
        },
    }));
});
</script>
