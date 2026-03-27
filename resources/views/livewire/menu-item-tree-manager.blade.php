<div
    class="fi-resource-relation-manager"
    x-data="{
        sortableInstances: [],

        init() {
            this.initSortable();

            Livewire.hook('morph.updated', ({ component }) => {
                if (component.id === $wire.__instance.id) {
                    this.$nextTick(() => this.initSortable());
                }
            });
        },

        initSortable() {
            this.destroySortable();

            this.$nextTick(() => {
                const root = this.$refs.treeRoot;
                if (!root) return;

                const lists = [root, ...root.querySelectorAll('.navcraft-tree-children')];

                lists.forEach(list => {
                    this.sortableInstances.push(
                        new Sortable(list, {
                            group: 'navcraft-menu-items',
                            animation: 150,
                            fallbackOnBody: true,
                            swapThreshold: 0.65,
                            handle: '[data-sortable-handle]',
                            draggable: '.navcraft-tree-item',
                            ghostClass: 'opacity-30',
                            onEnd: () => {
                                const tree = this.serializeTree(root);
                                $wire.reorder(tree);
                            }
                        })
                    );
                });
            });
        },

        destroySortable() {
            this.sortableInstances.forEach(instance => instance.destroy());
            this.sortableInstances = [];
        },

        serializeTree(list) {
            if (!list) return [];

            return [...list.querySelectorAll(':scope > .navcraft-tree-item')].map(el => ({
                id: parseInt(el.dataset.id),
                children: this.serializeTree(el.querySelector(':scope > .navcraft-tree-children'))
            }));
        }
    }"
>
    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        {{-- Header --}}
        <div class="fi-ta-header flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
            <div class="grid flex-1 gap-y-1">
                <h3 class="fi-ta-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    Menu Items
                </h3>
            </div>

            <div class="flex shrink-0 items-center gap-3">
                {{ $this->addItemAction }}
            </div>
        </div>

        {{-- Tree --}}
        <div class="fi-ta-content overflow-x-auto border-t border-gray-200 dark:border-white/10">
            @php
                $items = $this->getTreeItems();
            @endphp

            @if($items->isEmpty())
                <div class="fi-ta-empty-state px-6 py-12">
                    <div class="fi-ta-empty-state-content mx-auto grid max-w-lg justify-items-center text-center">
                        <div class="fi-ta-empty-state-icon-ctn mb-4 rounded-full bg-gray-100 p-3 dark:bg-gray-500/20">
                            <x-heroicon-o-bars-3 class="fi-ta-empty-state-icon h-6 w-6 text-gray-500 dark:text-gray-400" />
                        </div>
                        <h4 class="fi-ta-empty-state-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                            No menu items
                        </h4>
                        <p class="fi-ta-empty-state-description text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Get started by adding your first menu item.
                        </p>
                    </div>
                </div>
            @else
                <ul x-ref="treeRoot" class="navcraft-tree-root" role="list">
                    @each('navcraft::components.menu-item-tree-node', $items, 'item')
                </ul>
            @endif
        </div>
    </div>

    <x-filament-actions::modals />
</div>
