<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$args = array(
    'taxonomy'     => 'product_cat',
    'orderby'      => 'name',
    'show_count'   => 0,
    'pad_counts'   => 0,
    'hierarchical' => 1,
    'title_li'     => '',
    'hide_empty'   => 0
);
$all_categories = get_categories( $args );

?>

<ul class="list-group list-group-root" >
    <?php// print_r($all_categories); ?>
    <?php foreach ($all_categories as $category) : ?>
        <?php if ( $category->category_parent == 0 ) : ?>
            <li class="list-group-item list-group-item-action" data-toggle="collapse" ><i class="glyphicon glyphicon-chevron-right"></i><?php echo $category->slug; ?></li>
            <?php
                $args['parent'] = $category->term_id;
                $sub_cats = get_categories( $args ); 
            ?>
            <ul class="list-group collapse" >
            <?php foreach ($sub_cats as $sub_cat) : ?>
                <li class="list-group-item list-group-item-action" data-toggle="collapse" ><i class="glyphicon glyphicon-chevron-right"></i>   - <?php echo $sub_cat->slug; ?></li>
                
                <?php
                    $args['parent'] = $sub_cat->term_id;
                    $sub_sub_cats = get_categories( $args ); 
                ?>
                <ul class="list-group collapse" >
                <?php foreach ($sub_sub_cats as $sub_sub_cat) : ?>
                    <li class="list-group-item list-group-item-action">      - - <?php echo $sub_sub_cat->slug; ?></li>
                <?php endforeach ?>
                </ul>

            <?php endforeach ?>

            </ul>

        <?php endif ?>
    <?php endforeach ?>
</ul>
<?php