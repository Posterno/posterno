<template>
	<wp-container id="pno-admin-header-wrapper">
		<wp-header height="auto" class="pno-admin-title-area">
			<wp-row :gutter="0" class="wrap">
				<wp-col :span="16">
					<h1>
						<slot></slot>
					</h1>
					<ul class="title-links hidden-sm-and-down">
						<li v-for="(item, index) in links" :key="index">
							<a :href="item.url" class="page-title-action" target="_blank">{{item.title}}</a>
						</li>
					</ul>
				</wp-col>
				<wp-col :span="8">
					<slot name="side"></slot>
				</wp-col>
			</wp-row>
		</wp-header>
	</wp-container>

</template>

<script>
export default {
	name: 'pno-admin-header',
	props: {
		links: '',
	}
}
</script>
