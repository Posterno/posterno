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


<style lang="scss" scoped>
	.pno-admin-banner {
		align-items: center;
		background-color: #7155b0;
		box-sizing: border-box;
		overflow: hidden;
		position: relative;
		top: 46px;
		width: 100%;

		@media (min-width: 601px) {
			align-items: stretch;
			display: flex;
			top: 0;
		}

		&__logo {
			padding: 25px;

			img {
				display: block;
				height: 3 * 25px;
			}
		}

		&__body {
			display: none;
			flex: 1;

			@media (min-width: 601px) {
				background-image: url(http://plugins.local/wp-content/plugins/wp-business-reviews/assets/dist/images/skyline-banner.png);
				background-repeat: repeat-x;
				background-position: left bottom;
				background-size: auto 80%;
				display: flex;
				justify-content: flex-end;
				padding: 25px;
			}
		}
	}
	.pno-admin-title-area {
		background-color: #fff;
		box-shadow: 0 1px 0 rgba(200,215,225,0.5), 0 1px 2px #DDD;
		padding: 15px 20px;
		.wrap {
			margin: 0;
			h1 {
				display: inline-block;
				padding: 0
			}
		}
		.title-links {
			display: inline-block;
			margin: 10px 0 0 20px;
			li {
				display: inline-block;
				margin-bottom: 0;
			}
		}
	}
</style>
