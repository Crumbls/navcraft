<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>

    <style>
        .nc-tree-children { padding-left: 1.5rem; }
        .nc-tree-dragging .nc-tree-children:empty { min-height: 2rem; border: 2px dashed #d1d5db; border-radius: 0.75rem; }
        .fi-fo-repeater-item + .nc-tree-children { margin-top: 0.25rem; }
        .nc-tree-item.sortable-ghost { opacity: 0.4; }
        .nc-tree-item { margin-bottom: 0.25rem; }
        .nc-tree-handle { cursor: grab; color: var(--gray-400); padding: 0.25rem; display: flex; align-items: center; }
        .nc-tree-handle:hover { color: var(--gray-500); }
        .nc-tree-actions { display: flex; align-items: center; gap: 0.125rem; opacity: 0; transition: opacity 0.15s; margin-left: auto; padding: 0.125rem; }
        .nc-tree-item:hover > .fi-fo-repeater-item .nc-tree-actions { opacity: 1; }
        .nc-tree-action-btn { padding: 0.25rem; background: none; border: none; cursor: pointer; color: var(--gray-500); display: flex; border-radius: 0.25rem; transition: all 0.1s; }
        .dark .nc-tree-action-btn { color: var(--gray-400); }
        .nc-tree-action-btn:hover { color: var(--primary-500); background: color-mix(in oklab, var(--primary-500) 10%, transparent); }
        .nc-tree-action-btn--danger:hover { color: var(--danger-500); background: color-mix(in oklab, var(--danger-500) 10%, transparent); }
        .nc-tree-action-btn svg { width: 0.8125rem; height: 0.8125rem; }
        .nc-tree-collapse-btn { padding: 0.125rem; background: none; border: none; cursor: pointer; color: var(--gray-400); display: flex; border-radius: 0.25rem; transition: transform 0.15s; }
        .nc-tree-collapse-btn svg { width: 0.75rem; height: 0.75rem; }
        .nc-tree-collapse-btn.nc-collapsed { transform: rotate(-90deg); }
        .nc-tree-inline-input { font-size: 0.875rem; font-weight: 500; color: var(--gray-950); background: transparent; border: none; border-bottom: 2px solid var(--primary-500); outline: none; padding: 0 0.125rem; min-width: 4rem; }
        .dark .nc-tree-inline-input { color: white; }
        .nc-tree-type-badge { font-size: 0.625rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.025em; padding: 0.125rem 0.375rem; border-radius: 0.375rem; line-height: 1; white-space: nowrap; }
        .nc-tree-type-badge--url { color: var(--primary-600); background: color-mix(in oklab, var(--primary-500) 10%, transparent); }
        .nc-tree-type-badge--route { color: var(--warning-600); background: color-mix(in oklab, var(--warning-500) 10%, transparent); }
        .nc-tree-type-badge--mega { color: var(--success-600); background: color-mix(in oklab, var(--success-500) 10%, transparent); }
        .nc-tree-item.nc-tree-item--dimmed > .fi-fo-repeater-item { opacity: 0.3; }
        .nc-tree-toolbar { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; }
        .nc-tree-search { flex: 1; font-size: 0.75rem; padding: 0.375rem 0.625rem; border: 1px solid var(--gray-200); border-radius: 0.5rem; background: transparent; color: var(--gray-700); outline: none; transition: border-color 0.15s; }
        .dark .nc-tree-search { border-color: var(--gray-700); color: var(--gray-200); }
        .nc-tree-search:focus { border-color: var(--primary-500); }
        .nc-tree-toolbar-btn { padding: 0.375rem; background: none; border: 1px solid var(--gray-200); border-radius: 0.375rem; cursor: pointer; color: var(--gray-500); display: flex; transition: all 0.1s; }
        .dark .nc-tree-toolbar-btn { border-color: var(--gray-700); color: var(--gray-400); }
        .nc-tree-toolbar-btn:hover:not(:disabled) { color: var(--primary-500); border-color: var(--primary-400); }
        .nc-tree-toolbar-btn:disabled { opacity: 0.3; cursor: default; }
        .nc-tree-toolbar-btn svg { width: 0.875rem; height: 0.875rem; }
        .nc-tree-add-btn { width: 100%; margin-top: 0.375rem; padding: 0.5rem; font-size: 0.75rem; font-weight: 500; color: var(--gray-400); border: 1px dashed var(--gray-200); border-radius: 0.75rem; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.375rem; transition: all 0.15s; }
        .dark .nc-tree-add-btn { border-color: var(--gray-700); color: var(--gray-500); }
        .nc-tree-add-btn:hover { color: var(--primary-500); border-color: var(--primary-400); background: color-mix(in oklab, var(--primary-500) 3%, transparent); }
        .nc-tree-add-btn svg { width: 0.875rem; height: 0.875rem; }
    </style>

    <div
        wire:ignore
        x-data="ncTreeBuilder({
            componentKey: @js($getKey()),
            initialItems: @js($initialItems),
        })"
        x-effect="$el.querySelector('.nc-tree-root').innerHTML = renderTree(); initSortables();"
    >
        <div class="fi-fo-repeater">
            <template x-if="items.length === 0">
                <div style="text-align:center; padding:2rem 1rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" style="width:3rem; height:3rem; margin:0 auto 0.75rem; color:var(--gray-300);">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <p style="font-size:0.875rem; font-weight:500; color:var(--gray-500); margin-bottom:0.25rem;">No menu items yet</p>
                    <p style="font-size:0.75rem; color:var(--gray-400);">Add your first item to start building the menu.</p>
                </div>
            </template>

            <div class="nc-tree-toolbar">
                <input type="text" class="nc-tree-search" placeholder="Search items..." x-model.debounce.200ms="searchQuery">
                <button type="button" class="nc-tree-toolbar-btn" title="Undo" :disabled="!canUndo" @click="undo()">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                </button>
                <button type="button" class="nc-tree-toolbar-btn" title="Redo" :disabled="!canRedo" @click="redo()">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 15l6-6m0 0l-6-6m6 6H9a6 6 0 0 0 0 12h3" /></svg>
                </button>
            </div>

            <div class="nc-tree-children nc-tree-root"></div>

            <button type="button" class="nc-tree-add-btn" @click="addItem()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Add item
            </button>
        </div>

        <div style="display:none !important; position:absolute; overflow:hidden; width:0; height:0;">
            {{ $getAction('editItem') }}
            {{ $getAction('addItem') }}
            {{ $getAction('deleteItem') }}
            {{ $getAction('duplicateItem') }}
            {{ $getAction('reorderItems') }}
            {{ $getAction('renameItem') }}
        </div>
    </div>

    @script
    <script>
        Alpine.data('ncTreeBuilder', (config) => ({
            componentKey: config.componentKey,
            items: config.initialItems || [],

            typeLabels: { url: 'URL', route: 'Route', mega: 'Mega Menu' },
            supportsChildren: ['url', 'route'],

            collapsed: {},
            editingId: null,
            searchQuery: '',
            history: [],
            historyIndex: -1,
            maxHistory: 30,

            pushHistory() {
                const snapshot = JSON.stringify(this.items);
                if (this.historyIndex >= 0 && this.history[this.historyIndex] === snapshot) return;
                this.history = this.history.slice(0, this.historyIndex + 1);
                this.history.push(snapshot);
                if (this.history.length > this.maxHistory) this.history.shift();
                this.historyIndex = this.history.length - 1;
            },

            undo() {
                if (this.historyIndex <= 0) return;
                this.historyIndex--;
                this.items = JSON.parse(this.history[this.historyIndex]);
            },

            redo() {
                if (this.historyIndex >= this.history.length - 1) return;
                this.historyIndex++;
                this.items = JSON.parse(this.history[this.historyIndex]);
            },

            get canUndo() { return this.historyIndex > 0; },
            get canRedo() { return this.historyIndex < this.history.length - 1; },

            toggleCollapse(id) { this.collapsed[id] = !this.collapsed[id]; },
            isCollapsed(id) { return !!this.collapsed[id]; },

            matchesSearch(item) {
                if (!this.searchQuery) return true;
                const q = this.searchQuery.toLowerCase();
                if (item.label.toLowerCase().includes(q)) return true;
                if (item.children) return item.children.some(child => this.matchesSearch(child));
                return false;
            },

            renderItem(item) {
                const canHaveChildren = this.supportsChildren.includes(item.type);
                const hasChildren = canHaveChildren && item.children && item.children.length > 0;
                const isCollapsed = this.isCollapsed(item.id);
                const childrenHtml = canHaveChildren && !isCollapsed ? item.children.map(child => this.renderItem(child)).join('') : '';
                const typeLabel = this.typeLabels[item.type] || item.type;
                const dimmed = this.searchQuery && !this.matchesSearch(item) ? ' nc-tree-item--dimmed' : '';
                const escapedLabel = item.label.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

                const collapseBtn = hasChildren
                    ? `<button type='button' class='nc-tree-collapse-btn ${isCollapsed ? "nc-collapsed" : ""}' data-action='collapse' data-id='${item.id}' title='${isCollapsed ? "Expand" : "Collapse"}'><svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' d='m19.5 8.25-7.5 7.5-7.5-7.5' /></svg></button>`
                    : '';

                const childrenBlock = canHaveChildren
                    ? `<div class='nc-tree-children' style='${isCollapsed ? "display:none" : ""}'>${childrenHtml}</div>`
                    : '';

                const childCount = hasChildren
                    ? `<span style='font-size:0.625rem; color:var(--gray-400); margin-left:0.25rem;'>(${item.children.length})</span>`
                    : '';

                return `<div class='nc-tree-item${dimmed}' data-id='${item.id}' data-type='${item.type}'><div class='fi-fo-repeater-item'><div class='fi-fo-repeater-item-header'><button type='button' class='nc-tree-handle'><svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' style='width:1.25rem; height:1.25rem;'><path stroke-linecap='round' stroke-linejoin='round' d='M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5' /></svg></button>${collapseBtn}<span class='fi-fo-repeater-item-header-label' data-action='inline-edit' data-id='${item.id}'>${escapedLabel}</span>${childCount}<span class='nc-tree-type-badge nc-tree-type-badge--${item.type}'>${typeLabel}</span><div class='nc-tree-actions'><button type='button' class='nc-tree-action-btn' data-action='edit' data-id='${item.id}' title='Edit'><svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' d='m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10' /></svg></button><button type='button' class='nc-tree-action-btn' data-action='duplicate' data-id='${item.id}' title='Duplicate'><svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' d='M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75' /></svg></button><button type='button' class='nc-tree-action-btn nc-tree-action-btn--danger' data-action='delete' data-id='${item.id}' title='Delete'><svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' d='m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0' /></svg></button></div></div></div>${childrenBlock}</div>`;
            },

            renderTree() {
                return this.items.map(item => this.renderItem(item)).join('');
            },

            initSortables() {
                this.$nextTick(() => {
                    const root = this.$root;
                    const supportsChildren = this.supportsChildren;

                    root.querySelectorAll('.nc-tree-children').forEach((el) => {
                        if (el._sortable) el._sortable.destroy();

                        el._sortable = new Sortable(el, {
                            group: 'nested',
                            animation: 150,
                            fallbackOnBody: true,
                            swapThreshold: 0.65,
                            handle: '.nc-tree-handle',
                            ghostClass: 'sortable-ghost',
                            onStart: () => { root.classList.add('nc-tree-dragging'); },
                            onMove: (evt) => {
                                const targetParent = evt.to.closest('.nc-tree-item');
                                if (!targetParent) return true;
                                return supportsChildren.includes(targetParent.dataset.type);
                            },
                            onEnd: () => {
                                root.classList.remove('nc-tree-dragging');
                                this.syncFromDom();
                                this.pushHistory();
                                this.persistReorder();
                            },
                        });
                    });
                });
            },

            syncFromDom() {
                const parse = (container) => {
                    return Array.from(container.children)
                        .filter(el => el.classList.contains('nc-tree-item'))
                        .map(el => {
                            const childContainer = el.querySelector('.nc-tree-children');
                            const id = parseInt(el.dataset.id);
                            const existing = this.findItem(this.items, id);
                            return {
                                id, label: el.querySelector('.fi-fo-repeater-item-header-label').textContent.trim(),
                                type: el.dataset.type || 'url',
                                url: existing?.url ?? '', route_name: existing?.route_name ?? '',
                                route_params: existing?.route_params ?? {}, mega_content: existing?.mega_content ?? { rows: [] },
                                target: existing?.target ?? '_self', css_class: existing?.css_class ?? '',
                                icon: existing?.icon ?? '', children: childContainer ? parse(childContainer) : [],
                            };
                        });
                };
                this.items = parse(this.$root.querySelector('.nc-tree-root'));
            },

            persistReorder() {
                const simplify = (items) => items.map(item => ({ id: item.id, children: simplify(item.children || []) }));
                $wire.mountAction('reorderItems', { items: simplify(this.items) }, { schemaComponent: this.componentKey });
            },

            addItem() {
                $wire.mountAction('addItem', {}, { schemaComponent: this.componentKey });
            },

            startInlineEdit(id) {
                const labelEl = this.$root.querySelector(`.nc-tree-item[data-id='${id}'] .fi-fo-repeater-item-header-label`);
                if (!labelEl || this.editingId === id) return;
                this.editingId = id;
                const currentLabel = labelEl.textContent.trim();
                const input = document.createElement('input');
                input.type = 'text';
                input.value = currentLabel;
                input.className = 'nc-tree-inline-input';
                input.style.width = Math.max(currentLabel.length * 8, 60) + 'px';

                const finish = () => {
                    const newLabel = input.value.trim();
                    if (newLabel && newLabel !== currentLabel) {
                        const item = this.findItem(this.items, id);
                        if (item) { item.label = newLabel; this.pushHistory(); }
                        $wire.mountAction('renameItem', { id, label: newLabel }, { schemaComponent: this.componentKey });
                    }
                    this.editingId = null;
                };

                input.addEventListener('blur', finish);
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') { e.preventDefault(); input.blur(); }
                    if (e.key === 'Escape') { input.value = currentLabel; input.blur(); }
                });

                labelEl.textContent = '';
                labelEl.appendChild(input);
                input.focus();
                input.select();
            },

            findAndRemove(items, id) {
                for (let i = 0; i < items.length; i++) {
                    if (items[i].id === id) { items.splice(i, 1); return true; }
                    if (this.findAndRemove(items[i].children, id)) return true;
                }
                return false;
            },

            findParentArray(items, id) {
                for (let i = 0; i < items.length; i++) {
                    if (items[i].id === id) return items;
                    const found = this.findParentArray(items[i].children, id);
                    if (found) return found;
                }
                return null;
            },

            findItem(items, id) {
                for (let i = 0; i < items.length; i++) {
                    if (items[i].id === id) return items[i];
                    const found = this.findItem(items[i].children, id);
                    if (found) return found;
                }
                return null;
            },

            handleAction(e) {
                const btn = e.target.closest('[data-action]');
                if (!btn) return;
                const action = btn.dataset.action;
                const id = parseInt(btn.dataset.id);

                if (action === 'edit') $wire.mountAction('editItem', { id }, { schemaComponent: this.componentKey });
                if (action === 'delete') $wire.mountAction('deleteItem', { id }, { schemaComponent: this.componentKey });
                if (action === 'duplicate') $wire.mountAction('duplicateItem', { id }, { schemaComponent: this.componentKey });
                if (action === 'collapse') this.toggleCollapse(id);
            },

            handleDblClick(e) {
                const label = e.target.closest('[data-action=inline-edit]');
                if (!label) return;
                this.startInlineEdit(parseInt(label.dataset.id));
            },

            init() {
                this.pushHistory();
                this.$root.addEventListener('click', (e) => this.handleAction(e));
                this.$root.addEventListener('dblclick', (e) => this.handleDblClick(e));

                Livewire.on('nc-tree-item-updated', (params) => {
                    const id = params.id ?? params[0]?.id;
                    const data = params.data ?? params[0]?.data;
                    const item = this.findItem(this.items, id);
                    if (!item || !data) return;
                    if (data.label) item.label = data.label;
                    if (data.type) item.type = data.type;
                    item.url = data.url ?? ''; item.route_name = data.route_name ?? '';
                    item.route_params = data.route_params ?? {}; item.mega_content = data.mega_content ?? { rows: [] };
                    item.target = data.target ?? '_self'; item.css_class = data.css_class ?? '';
                    item.icon = data.icon ?? ''; this.pushHistory();
                });

                Livewire.on('nc-tree-item-added', (params) => {
                    const item = params.item ?? params[0]?.item;
                    if (item) { this.items.push(item); this.pushHistory(); }
                });

                Livewire.on('nc-tree-item-deleted', (params) => {
                    const id = params.id ?? params[0]?.id;
                    if (id) { this.findAndRemove(this.items, id); this.pushHistory(); }
                });

                Livewire.on('nc-tree-item-duplicated', (params) => {
                    const item = params.item ?? params[0]?.item;
                    if (!item) return;
                    const parent = this.findParentArray(this.items, item.id);
                    if (!parent) this.items.push(item);
                    this.pushHistory();
                });

                Livewire.on('nc-tree-item-renamed', (params) => {
                    const id = params.id ?? params[0]?.id;
                    const label = params.label ?? params[0]?.label;
                    const item = this.findItem(this.items, id);
                    if (item && label) item.label = label;
                });

                this.$nextTick(() => { this.initSortables(); });
            },
        }));
    </script>
    @endscript
</x-dynamic-component>
