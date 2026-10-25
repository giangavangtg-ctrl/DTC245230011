<?php
/**
 * Bài viết mặc định.
 *
 * @package TechVietStore
 */

get_header();
techviet_breadcrumbs();
?>

<?php while ( have_posts() ) : the_post(); ?>
	<article class="tv-article">
		<header class="tv-page-hero">
			<div class="tv-container tv-article__head">
				<span class="tv-eyebrow"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?> · TechViet Blog</span>
				<h1><?php the_title(); ?></h1>
				<p class="tv-article__meta">Đăng bởi <?php the_author(); ?> · <?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></p>
			</div>
		</header>

		<div class="tv-container tv-article__body">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="tv-article__thumb"><?php the_post_thumbnail( 'techviet-wide' ); ?></figure>
			<?php endif; ?>

			<div class="tv-prose">
				<?php the_content(); ?>
			</div>

			<nav class="tv-post-nav" aria-label="Điều hướng bài viết">
				<span><?php previous_post_link( '%link', '← %title' ); ?></span>
				<span><?php next_post_link( '%link', '%title →' ); ?></span>
			</nav>
		</div>
	</article>
<?php endwhile; ?>

<?php
get_footer();
