<?php
/**
 * Plugin Name:       Proweaver Google Reviews
 * Description:       A Proweaver plugin for embedding Google Reviews in your website using a shortcode.
 * Version:           1.1.0
 * Author:            Proweaver Night Shift SP Team
 * Author URI:        https://www.proweaver.com/
**/

// Shortcode callback function
function prow_google_reviews_shortcode() {
    // Get the saved Place ID and sorting from the options
    $place_id = get_option('proweaver_google_reviews_place_id');
    $sorting_option = get_option('proweaver_google_reviews_sorting_option');

    // Fetch Google Reviews using the API
    $reviews_data = fetch_google_reviews($place_id, $sorting_option);
    $rating = $reviews_data['overall_rating'];
    $overall_rating = $rating;
    $slideIndex = 0;

    // Prepare the output HTML
    $output = '<div id="reviews_header">
                <figure><img src="' . plugin_dir_url(__FILE__) . '/images/google.svg" alt="Google"> <figcaption>Ratings</figcaption></figure>
                <h2>' . $rating . ' <span>';

                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= floor($rating)) {
                        $output .= '<i class="fa fa-star" aria-hidden="true"></i> '; // Font Awesome full star icon
                    } else if ($i - $rating <= 0.5) {
                        $output .= '<i class="fa fa-star-half-o" aria-hidden="true"></i> '; // Font Awesome half-star icon
                    } else {
                        $output .= '<i class="fa fa-star-o" aria-hidden="true"></i> '; // Font Awesome empty star icon
                    }
                }
        $output .= '</span></h2>
            </div>';
    $output .= '
            <div id="reviews_frame">
                <div class="glide">
                    <div class="glide__track" data-glide-el="track">
                        <ul class="glide__slides">';

                        $reviews = $reviews_data['reviews'];

                        foreach ($reviews as $review) {
                            $output .= '
                            <li class="glide__slide">';
                                $output .= '
                                <section class="reviews">';
                                    $output .= '
                                    <div class="author">';
                                        $output .= '
                                        <figure><img src="' . $review['profile_photo_url'] . '" alt="Avatar"></figure>';
                                        $output .= '
                                        <h2>' . $review['author_name'] . ' <span>' . $review['relative_time_description'] . '</span></h2>';
                                    $output .= '
                                    </div>';
                                    // Add "Read More" and "Read Less" functionality to the review text
                                    $review_text = $review['text'];
                                    $output .= '<p class="review-text">';
                                    if (strlen($review_text) > 200) {
                                        $output .= '<span class="review-text-more">' . substr($review_text, 0, 200) . '... </span>';
                                        $output .= '<a href="javascript:;" class="read-more-link">Read More</a>';
                                        $output .= '<span class="review-text-less" style="display: none;">' . $review_text . ' </span>';
                                        $output .= '<a href="javascript:;" class="read-less-link" style="display: none;">Read Less</a>';
                                    } else {
                                        $output .= $review_text;
                                    }
                                    $output .= '</p>';
                                    // Display the rating as Font Awesome stars
                                    $output .= '
                                    <span>';
                                    $rating = $review['rating'];
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                $output .= '<i class="fa fa-star" aria-hidden="true"></i> '; // Font Awesome star icon
                                            } else {
                                                $output .= '<i class="fa fa-star-o" aria-hidden="true"></i> '; // Font Awesome empty star icon
                                            }
                                        }
                                    $output .= '</span>';
                                    $output .= '
                                </section>';
                            $output .= '
                            </li>';
                        }
                        $output .= '
                        </ul>';
                if (get_option('proweaver_google_reviews_show_controls', 1)) {
                    $output .= '
                        <div class="glide__arrows" data-glide-el="controls">
                            <button class="glide__arrow glide__arrow--left" data-glide-dir="<"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
                            <button class="glide__arrow glide__arrow--right" data-glide-dir=">"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
                        </div>';
                }
                if (get_option('proweaver_google_reviews_show_bullet_pagination', 1)) {
                    $output .= '<div class="glide__bullets" data-glide-el="controls[nav]">';
                        foreach ($reviews as $index => $review) {
                            $output .= '<button class="glide__bullet" data-glide-dir="='. $index .'"></button>';
                        }
                    $output .= '</div>';
                }
                $output .= '
                    </div>';
                $output .= '
                </div>';
            $output .= '
            </div>
';

            // Add JavaScript to handle "Read More" and "Read Less" functionality
            if (is_front_page()) {
                $output .= '
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        var autoplayInterval = ' . absint( get_option( 'proweaver_google_reviews_autoplay_interval', 5000 ) ) . ';
                        new Glide(".glide", {
                            type: "carousel",
                            perView: 4,
                            autoplay: autoplayInterval,
                            peek: 0,
                            pagination: {
                                clickable: true,
                            },
                            breakpoints: {
                                1024: {
                                    perView: 3
                                },
                                768: {
                                    perView: 2
                                },
                                600: {
                                    perView: 1
                                }
                            }
                        }).mount();

                        var reviewTextElements = document.getElementsByClassName("review-text");
                
                        Array.from(reviewTextElements).forEach(function(element) {
                            var reviewText = element.querySelector(".review-text-more");
                            var readMoreLink = element.querySelector(".read-more-link");
                            var reviewTextLess = element.querySelector(".review-text-less");
                            var readLessLink = element.querySelector(".read-less-link");
            
                            if (reviewText && readMoreLink && reviewTextLess && readLessLink) {
                                readMoreLink.addEventListener("click", function(e) {
                                    e.preventDefault();
                                    reviewText.style.display = "none";
                                    readMoreLink.style.display = "none";
                                    reviewTextLess.style.display = "inline";
                                    readLessLink.style.display = "inline";
                                });
            
                                readLessLink.addEventListener("click", function(e) {
                                    e.preventDefault();
                                    reviewText.style.display = "inline";
                                    readMoreLink.style.display = "inline";
                                    reviewTextLess.style.display = "none";
                                    readLessLink.style.display = "none";
                                });
                            }
                        });
                    });
                </script>
                ';
            } else {
                $output .= '
                <script>
                    var autoplayInterval = ' . absint( get_option( 'proweaver_google_reviews_autoplay_interval', 5000 ) ) . ';
                    new Glide(".glide", {
                        type: "carousel",
                        perView: 4,
                        autoplay: autoplayInterval,
                        peek: 0,
                        pagination: {
                            clickable: true,
                        },
                        breakpoints: {
                            1024: {
                                perView: 3
                            },
                            768: {
                                perView: 2
                            },
                            600: {
                                perView: 1
                            }
                        }
                    }).mount();

                    var reviewTextElements = document.getElementsByClassName("review-text");
            
                    Array.from(reviewTextElements).forEach(function(element) {
                        var reviewText = element.querySelector(".review-text-more");
                        var readMoreLink = element.querySelector(".read-more-link");
                        var reviewTextLess = element.querySelector(".review-text-less");
                        var readLessLink = element.querySelector(".read-less-link");
        
                        if (reviewText && readMoreLink && reviewTextLess && readLessLink) {
                            readMoreLink.addEventListener("click", function(e) {
                                e.preventDefault();
                                reviewText.style.display = "none";
                                readMoreLink.style.display = "none";
                                reviewTextLess.style.display = "inline";
                                readLessLink.style.display = "inline";
                            });
        
                            readLessLink.addEventListener("click", function(e) {
                                e.preventDefault();
                                reviewText.style.display = "inline";
                                readMoreLink.style.display = "inline";
                                reviewTextLess.style.display = "none";
                                readLessLink.style.display = "none";
                            });
                        }
                    });
                </script>
                ';
            }

    return $output;
}

// Register the shortcode
add_shortcode('prow_google_reviews', 'prow_google_reviews_shortcode');

// Fetch Google Reviews using Google Places API
function fetch_google_reviews($place_id, $sorting_option) {
    // Google Places API endpoint
    $api_url = 'https://maps.googleapis.com/maps/api/place/details/json';

    // Your Google API Key
    $api_key = 'YOUR_API_KEY';

    // Query parameters
    $params = array(
        'place_id' => $place_id,
        'key' => $api_key,
        'reviews_sort' => $sorting_option,
        'fields' => 'rating,reviews',
    );

    // Build the API URL with query parameters
    $url = $api_url . '?' . http_build_query($params);

    // Initialize cURL session
    $curl = curl_init();

    // Set cURL options
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);

    // Execute the cURL request
    $response = curl_exec($curl);

    // Check for cURL errors
    if (curl_errno($curl)) {
        // Handle the error case
        $error_message = curl_error($curl);
        // Log or display the error message as needed
        curl_close($curl);
        return array();
    }

    // Close the cURL session
    curl_close($curl);

    // Parse the JSON response
    $data = json_decode($response, true);

    // Check if the response contains reviews data
    if (isset($data['result']['reviews'])) {
        // Extract the rating and reviews array
        $rating = $data['result']['rating'];
        $reviews = $data['result']['reviews'];

        // Prepare the final array of reviews
        $formatted_reviews = array();
        foreach ($reviews as $review) {
            $formatted_review = array(
                'author_name' => $review['author_name'],
                'profile_photo_url' => $review['profile_photo_url'],
                'relative_time_description' => $review['relative_time_description'],
                'text' => $review['text'],
                'rating' => $review['rating'],
            );
            $formatted_reviews[] = $formatted_review;
        }

        return array(
            'reviews' => $formatted_reviews,
            'overall_rating' => number_format($rating, 1),
        );
    }

    // No reviews found
    return array();
}

// Add settings page
function proweaver_google_reviews_settings_page()
{
    add_options_page(
        'Proweaver Google Reviews Settings',
        'Proweaver Google Reviews',
        'manage_options',
        'proweaver-google-reviews',
        'proweaver_google_reviews_render_settings'
    );
}
add_action('admin_menu', 'proweaver_google_reviews_settings_page');

// Render settings page
function proweaver_google_reviews_render_settings()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // Enqueue the custom CSS file
    wp_enqueue_style('proweaver-google-reviews-admin', plugin_dir_url(__FILE__) . 'css/admin.css');

    // Get the saved Place ID
    $place_id = get_option('proweaver_google_reviews_place_id');

    // Get the saved autoplay interval
    $autoplay_interval = get_option('proweaver_google_reviews_autoplay_interval', 5000);

    // Get the saved Glide.js control option
    $show_controls = get_option('proweaver_google_reviews_show_controls', 1);

    // Get the saved Glide.js bullet pagination option
    $show_bullet_pagination = get_option('proweaver_google_reviews_show_bullet_pagination', 1);

    // Get saved option, defaulting to "most_relevant"
    $sorting_option = get_option('proweaver_google_reviews_sorting_option', 'most_relevant');
    ?>

    <div class="wrap">
        <h1>Proweaver Google Reviews Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('proweaver_google_reviews_settings');
            do_settings_sections('proweaver_google_reviews_settings');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="proweaver_google_reviews_place_id">Google Place ID</label></th>
                    <td><input type="text" id="proweaver_google_reviews_place_id" name="proweaver_google_reviews_place_id" value="<?php echo esc_attr($place_id); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="proweaver_google_reviews_sorting_option">Sorting By</label></th>
                    <td>
                        <select id="proweaver_google_reviews_sorting_option" name="proweaver_google_reviews_sorting_option">
                            <option value="most_relevant" <?php selected($sorting_option, 'most_relevant'); ?>>Most Relevant</option>
                            <option value="newest" <?php selected($sorting_option, 'newest'); ?>>Newest</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="proweaver_google_reviews_autoplay_interval">Autoplay Interval (in milliseconds)</label></th>
                    <td><input type="number" id="proweaver_google_reviews_autoplay_interval" name="proweaver_google_reviews_autoplay_interval" value="<?php echo esc_attr($autoplay_interval); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="proweaver_google_reviews_show_controls">Show Arrow Controls</label></th>
                    <td><input type="checkbox" id="proweaver_google_reviews_show_controls" name="proweaver_google_reviews_show_controls" value="1" <?php checked($show_controls, 1); ?>></td>
                </tr>
                <tr>
                    <th scope="row"><label for="proweaver_google_reviews_show_bullet_pagination">Show Bullet Pagination</label></th>
                    <td><input type="checkbox" id="proweaver_google_reviews_show_bullet_pagination" name="proweaver_google_reviews_show_bullet_pagination" value="1" <?php checked($show_bullet_pagination, 1); ?>></td>
                </tr>
            </table>
            <?php
            submit_button('Save Settings');
            ?>
        </form>

        <h2>Shortcode</h2>
        <p>Use the following shortcode to display the Proweaver Google Reviews:</p>
        <code class="shortcode-code" onclick="copyShortcode()">[prow_google_reviews]</code>
    </div>

    <script>
        function copyShortcode() {
            const shortcodeElement = document.querySelector('.shortcode-code');
            const range = document.createRange();
            range.selectNode(shortcodeElement);
            window.getSelection().addRange(range);

            try {
                document.execCommand('copy');
                shortcodeElement.style.color = '#3ca31f';
                shortcodeElement.textContent = 'Shortcode Copied!';
                setTimeout(function() {
                    shortcodeElement.style.color = '#52585e';
                    shortcodeElement.textContent = '[prow_google_reviews]';
                }, 2000);
            } catch (err) {
                console.error('Failed to copy shortcode', err);
            }

            window.getSelection().removeAllRanges();
        }
    </script>

    <?php
}

function google_reviews_register_settings()
{
    register_setting('proweaver_google_reviews_settings', 'proweaver_google_reviews_place_id');
    register_setting('proweaver_google_reviews_settings', 'proweaver_google_reviews_sorting_option');
    register_setting('proweaver_google_reviews_settings', 'proweaver_google_reviews_autoplay_interval');
    register_setting('proweaver_google_reviews_settings', 'proweaver_google_reviews_show_controls');
    register_setting('proweaver_google_reviews_settings', 'proweaver_google_reviews_show_bullet_pagination');
}
add_action('admin_init', 'google_reviews_register_settings');

// Enqueue scripts and styles
function enqueue_google_reviews_scripts()
{
    // Enqueue the Glide.js script
    wp_enqueue_script('glide-js', plugin_dir_url(__FILE__) . 'js/glide.min.js', array(), '3.4.1', true);
    
    // Enqueue the widget styles
    wp_enqueue_style( 'style', plugin_dir_url( __FILE__ ) . 'css/style.min.css');

    // Enqueue the Font Awesome CSS
    wp_enqueue_style('font-awesome', plugin_dir_url(__FILE__) . 'css/font-awesome.min.css');

    // Enqueue Glide.js CSS
    wp_enqueue_style('glide-css', plugin_dir_url(__FILE__) . 'css/glide.core.min.css', array(), '3.4.1');

    // Enqueue Glide.js Theme CSS
    wp_enqueue_style('glide-theme-css', plugin_dir_url(__FILE__) . 'css/glide.theme.min.css', array(), '3.4.1');
}
add_action( 'wp_enqueue_scripts', 'enqueue_google_reviews_scripts' );