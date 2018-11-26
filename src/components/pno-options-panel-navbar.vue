<template>
	<div class="wp-filter-bar-wrapper">
		<div class="wp-filter">
			<ul class="filter-links">
				<li>
					<a
						v-for="(tabItem, index) in tabItems"
						:key="index"
						:class="[ 'filter-nav-tab', { ' current': newValue === index, 'is-disabled': tabItem.disabled }]"
						@click="tabClick(index)"
						href="javascript:void(0)"
						>
						{{ tabItem.label }}
					</a>
				</li>
			</ul>
		</div>
		<section class="tab-content">
			<slot/>
		</section>
	</div>
</template>

<script>
    export default {
        name: 'pno-options-panel-navbar',
        props: {
            value: [String, Number],
        },
        data() {
            return {
                newValue: this.value || 0,
                tabItems: [],
                _isTabs: true // Used internally by TabItem
            }
        },
        watch: {
            /**
             * When v-model is changed set the new active tab.
             */
            value(value) {
                this.changeTab(this.newValue, value)
                this.newValue = value
            },

            /**
             * When tab-items are updated, set active one.
             */
            tabItems() {
                if (this.tabItems.length) {
                    this.tabItems[this.newValue].isActive = true
                }
            }
        },
        methods: {
            /**
             * Change the active tab.
             */
            changeTab(oldIndex, newIndex) {
                if (oldIndex === newIndex) return

                this.tabItems[oldIndex].deactivate(oldIndex, newIndex)
                this.tabItems[newIndex].activate(oldIndex, newIndex)
            },

            /**
             * Tab click listener, emit events and set new active tab.
             */
            tabClick(value) {
                this.$emit('input', value)
                this.$emit('change', value)
                this.changeTab(this.newValue, value)
                this.newValue = value
            }
        },
        mounted() {
            if (this.tabItems.length) {
                this.tabItems[this.newValue].isActive = true
            }
        }
    }
</script>
