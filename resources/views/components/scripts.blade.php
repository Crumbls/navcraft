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
        menuId: config.menuId || '',

        toggle(id) {
            const wasOpen = this.openMenu === id;
            this.openMenu = wasOpen ? null : id;
            this.$dispatch(wasOpen ? 'navcraft:close' : 'navcraft:open', {
                menuId: this.menuId,
                itemId: id,
            });
        },

        open(id) {
            this.openMenu = id;
            this.$dispatch('navcraft:open', { menuId: this.menuId, itemId: id });
        },

        close(id) {
            if (this.openMenu === id) {
                this.openMenu = null;
                this.$dispatch('navcraft:close', { menuId: this.menuId, itemId: id });
            }
        },

        closeAll() {
            if (this.openMenu) {
                this.$dispatch('navcraft:close', { menuId: this.menuId, itemId: this.openMenu });
            }
            this.openMenu = null;
            this.mobileOpen = false;
            this.mobileExpanded = null;
        },

        hoverOpen(id) {
            if (this.hoverMode !== 'hover') return;
            clearTimeout(this.hoverTimeout);
            if (this.openMenu !== id) {
                this.openMenu = id;
                this.$dispatch('navcraft:open', { menuId: this.menuId, itemId: id });
            }
        },

        hoverClose(id) {
            if (this.hoverMode !== 'hover') return;
            this.hoverTimeout = setTimeout(() => {
                if (this.openMenu === id) {
                    this.openMenu = null;
                    this.$dispatch('navcraft:close', { menuId: this.menuId, itemId: id });
                }
            }, 150);
        },

        navigate(url, label, itemId) {
            this.$dispatch('navcraft:navigate', {
                menuId: this.menuId,
                itemId: itemId,
                label: label,
                url: url,
            });
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
