The Flynt Core Plugin can be used with any WordPress theme. To use it, just install it just copy it into your plugins folder and you are good to go.

## Example

We are going to extract the `div.site-branding` from twentysixteen's **header.php** into a module to demonstrate how you can make parts of your tepmlate reusable and also more structured, separating data logic from the template itself.

Here is the relevant html from twentysixteen's **header.php**.

```html
...
<div class="site-branding">
  <?php twentysixteen_the_custom_logo(); ?>

  <?php if ( is_front_page() && is_home() ) : ?>
    <h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
  <?php else : ?>
    <p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
  <?php endif;
  $description = get_bloginfo( 'description', 'display' );
  if ( $description || is_customize_preview() ) : ?>
    <p class="site-description"><?php echo $description; ?></p>
  <?php endif; ?>
</div><!-- .site-branding -->
...
```
