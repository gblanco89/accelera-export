<?php
/**
 * Module Name: Database checker
 * Description: Checks various aspects of the database
 *
 * @since 1.0.0
 */

/**
 * From a number and a total, returns the percentage, including '%'
 *
 * @since 1.0.0
 *
 * @param mixed $current A number.
 * @param mixed $total The total.
 * @return string Returns the percentage taking the total with a trailing '%'.
 * @return string Returns the total if the value is not numeric.
 */
function format_percentage( $current, $total ) {
    if ( is_numeric( $current ) || is_numeric( $total ) ) {
        return ( $total > 0 ? round( ( $current / $total ) * 100, 2 ) : 0 ) . '%';
    } else {
        return $total;
    }
}

/**
 * Callback for `persistent_object_cache` health check.
 *
 * @since 1.0.0
 *
 * @return string 'A' if Pers. Object Cache is used.
 * @return string 'B' if Pers. Object Cache should be used.
 * @return string 'C' if Pers. Object Cache shouldn't be used.
 */
function accelera_object_cache_check() {
    $result = 'A';

    if ( wp_using_ext_object_cache() ) {
        return $result;
    }

    if ( ! acc_should_suggest_persistent_object_cache() ) {
        $result = 'C';
        return $result;
    }

    return 'B';
}

/**
 * Determines whether to suggest using a persistent object cache.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return bool Whether to suggest using a persistent object cache.
 */
function acc_should_suggest_persistent_object_cache() {
    global $wpdb;

    if ( is_multisite() ) {
        return true;
    }

    // Thresholds used to determine whether to suggest the use of a persistent object cache.
    $thresholds = array(
            'alloptions_count' => 500,
            'alloptions_bytes' => 100000,
            'comments_count'   => 1000,
            'options_count'    => 1000,
            'posts_count'      => 1000,
            'terms_count'      => 1000,
            'users_count'      => 1000,
    );

    $alloptions = wp_load_alloptions();

    if ( $thresholds['alloptions_count'] < count( $alloptions ) ) {
        return true;
    }
    if ( $thresholds['alloptions_bytes'] < strlen( serialize( $alloptions ) ) ) {
        return true;
    }

    $table_names = implode( "','", array( $wpdb->comments, $wpdb->options, $wpdb->posts, $wpdb->terms, $wpdb->users ) );

    // With InnoDB the `TABLE_ROWS` are estimates, which are accurate enough and faster to retrieve than individual `COUNT()` queries.
    $results = $wpdb->get_results(
        $wpdb->prepare(
            // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT TABLE_NAME AS 'table', TABLE_ROWS AS 'rows', SUM(data_length + index_length) as 'bytes'
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = %s
            AND TABLE_NAME IN ('$table_names')
            GROUP BY TABLE_NAME;",
            // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            DB_NAME
        ),
        OBJECT_K
    );

    $threshold_map = array(
        'comments_count' => $wpdb->comments,
        'options_count'  => $wpdb->options,
        'posts_count'    => $wpdb->posts,
        'terms_count'    => $wpdb->terms,
        'users_count'    => $wpdb->users,
    );

    foreach ( $threshold_map as $threshold => $table ) {
        if ( $thresholds[ $threshold ] <= $results[ $table ]->rows ) {
            return true;
        }
    }

    return false;
}

/**
 * Executes all the queries to check the DB.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param array &$variables_db An array of various counters for database information
 */
function acc_queries( &$variables_db ) {

    global $wpdb;

    // Counting db totals
    $variables_db['posts_total'] = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts" );
    $variables_db['postmeta_total'] = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta" );
    $variables_db['commentmeta_total'] = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->commentmeta" );
    $variables_db['usersmeta_total'] = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->usermeta" );
    $variables_db['termmeta_total'] = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->termmeta" );
    $variables_db['termrelation_total'] = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->term_relationships" );
    $variables_db['options_total'] = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->options" );

    // Particular items totals
    $variables_db['revisions'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = %s", 'revision' ) );
    $variables_db['orphaned_postmeta'] = $wpdb->get_var( "SELECT COUNT(meta_id) FROM $wpdb->postmeta WHERE post_id NOT IN (SELECT ID FROM $wpdb->posts)" );
    $variables_db['orphaned_commentmeta'] = $wpdb->get_var( "SELECT COUNT(meta_id) FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_ID FROM $wpdb->comments)" );
    $variables_db['orphaned_usermeta'] = $wpdb->get_var( "SELECT COUNT(umeta_id) FROM $wpdb->usermeta WHERE user_id NOT IN (SELECT ID FROM $wpdb->users)" );
    $variables_db['orphaned_termmeta'] = $wpdb->get_var( "SELECT COUNT(meta_id) FROM $wpdb->termmeta WHERE term_id NOT IN (SELECT term_id FROM $wpdb->terms)" );
    $variables_db['oembed'] = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(meta_id) FROM $wpdb->postmeta WHERE meta_key LIKE(%s)", '%_oembed_%' ) );
    $variables_db['acc_objectcache'] = accelera_object_cache_check();
    $variables_db['orphaned_termrelation'] = $wpdb->get_var( "SELECT COUNT(object_id) FROM $wpdb->term_relationships AS tr INNER JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy != 'link_category' AND tr.object_id NOT IN (SELECT ID FROM $wpdb->posts)" ); // phpcs:ignore

    // Duplicated postmeta
    $query = $wpdb->get_col( $wpdb->prepare( "SELECT COUNT(meta_id) AS count FROM $wpdb->postmeta GROUP BY post_id, meta_key, meta_value HAVING count > %d", 1 ) );
    if ( is_array( $query ) ) {
        $variables_db['duplicated_postmeta'] = array_sum( array_map( 'intval', $query ) );
    }

    // Duplicated commentmeta
    $query = $wpdb->get_col( $wpdb->prepare( "SELECT COUNT(meta_id) AS count FROM $wpdb->commentmeta GROUP BY comment_id, meta_key, meta_value HAVING count > %d", 1 ) );
    if ( is_array( $query ) ) {
        $variables_db['duplicated_commentmeta'] = array_sum( array_map( 'intval', $query ) );
    }

    // Duplicated usermeta
    $query = $wpdb->get_col( $wpdb->prepare( "SELECT COUNT(umeta_id) AS count FROM $wpdb->usermeta GROUP BY user_id, meta_key, meta_value HAVING count > %d", 1 ) );
    if ( is_array( $query ) ) {
        $variables_db['duplicated_usermeta'] = array_sum( array_map( 'intval', $query ) );
    }

    // Duplicated termmeta
    $query = $wpdb->get_col( $wpdb->prepare( "SELECT COUNT(meta_id) AS count FROM $wpdb->termmeta GROUP BY term_id, meta_key, meta_value HAVING count > %d", 1 ) );
    if ( is_array( $query ) ) {
        $variables_db['duplicated_termmeta'] = array_sum( array_map( 'intval', $query ) );
    }

    // 5 Biggest tables
    $query = $wpdb->get_results( "SELECT TABLE_NAME AS `Table`, ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024),2) AS `Size` FROM information_schema.TABLES WHERE TABLE_SCHEMA = \"$wpdb->dbname\" ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC LIMIT 5" );
    $variables_db['biggest_tables'] = ''; // Empty the default '-'
    for ( $i = 0; $i <= 4; $i++ ) {
        $variables_db['biggest_tables'] .= $query[ $i ]->Table . ' (' . $query[ $i ]->Size . ' MB)';
        if ( $i < 3 ) {
            $variables_db['biggest_tables'] .= ', ';
        }
        elseif ( 3 === $i ) {
            $variables_db['biggest_tables'] .= ' and ';
        }
    }

    // Autoloads size (in KB)
    $autoloads_result = $wpdb->get_results("SELECT SUM(LENGTH(option_value)/1024.0) as autoload_size FROM $wpdb->options WHERE autoload='yes'");
    foreach( $autoloads_result as $object=>$uno ){
        $variables_db['autoloads'] = round( $uno->autoload_size );
    }
}

/*
* Database export - building arrays to put in CSV
*/
$dbheaders = array( 'Details', 'Count', '% of' );
$dbtitles = array( 'Revisions', 'Orphaned Post Meta', 'Duplicated Post Meta', 'oEmbed Caches In Post Meta', 'Orphaned Comment Meta', 'Duplicated Comment Meta', 'Orphaned User Meta', 'Duplicated User Meta', 'Orphaned Term Meta', 'Duplicated Term Meta', 'Orphaned Term Relationship', 'Object Cache', 'Biggest tables', 'Autoloads' );
$variables_db = array(
    'revisions' => '-',
    'orphaned_postmeta' => '-',
    'duplicated_postmeta' => '-',
    'oembed' => '-',
    'orphaned_commentmeta' => '-',
    'duplicated_commentmeta' => '-',
    'orphaned_usermeta' => '-',
    'duplicated_usermeta' => '-',
    'orphaned_termmeta' => '-',
    'duplicated_termmeta' => '-',
    'orphaned_termrelation' => '-',
    'acc_objectcache' => '-',
    'autoloads' => '-',
    'posts_total' => '-',
    'postmeta_total' => '-',
    'commentmeta_total' => '-',
    'usersmeta_total' => '-',
    'termmeta_total' => '-',
    'termrelation_total' => '-',
    'biggest_tables' => '-',
); // Default as '-' in case we skip db check

// Only execute the queries if we chose to not skip the db checks
if ( isset( $_REQUEST['skip'] ) ) {
    if ( $_REQUEST['skip'] !== 'db' ) {
        acc_queries( $variables_db );
    }
} else {
    acc_queries( $variables_db );
}

// Building arrays to parse later in the CSV
$particular_totals = array(
    $variables_db['revisions'],
    $variables_db['orphaned_postmeta'],
    $variables_db['duplicated_postmeta'],
    $variables_db['oembed'],
    $variables_db['orphaned_commentmeta'],
    $variables_db['duplicated_commentmeta'],
    $variables_db['orphaned_usermeta'],
    $variables_db['duplicated_usermeta'],
    $variables_db['orphaned_termmeta'],
    $variables_db['duplicated_termmeta'],
    $variables_db['orphaned_termrelation'],
    $variables_db['acc_objectcache'],
    $variables_db['biggest_tables'],
    $variables_db['autoloads'],
);
$dbtotals = array(
    $variables_db['posts_total'],
    $variables_db['postmeta_total'],
    $variables_db['postmeta_total'],
    $variables_db['postmeta_total'],
    $variables_db['commentmeta_total'],
    $variables_db['commentmeta_total'],
    $variables_db['usersmeta_total'],
    $variables_db['usersmeta_total'],
    $variables_db['termmeta_total'],
    $variables_db['termmeta_total'],
    $variables_db['termrelation_total'],
    '-',
    '-',
    '-',
);