<?php
/**
 * Plugin Name: BaoProd JobBoard Sync
 * Description: Synchronise les offres d'emploi avec BaoProd Workforce Suite
 * Version: 1.0.0
 * Author: BaoProd
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BaoProdJobBoardSync {
    
    private $api_url;
    private $api_key;
    private $tenant_id;
    
    public function __construct() {
        $this->api_url = get_option('baoprod_api_url', 'http://localhost:9000/api');
        $this->api_key = get_option('baoprod_api_key', '');
        $this->tenant_id = get_option('baoprod_tenant_id', 1);
        
        // WordPress hooks
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('baoprod_jobs', array($this, 'jobs_shortcode'));
        add_shortcode('baoprod_job_form', array($this, 'application_form_shortcode'));
        
        // Admin hooks
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        
        // AJAX hooks
        add_action('wp_ajax_submit_job_application', array($this, 'submit_application'));
        add_action('wp_ajax_nopriv_submit_job_application', array($this, 'submit_application'));
    }
    
    public function init() {
        // Create custom post type for cached jobs
        register_post_type('baoprod_job', array(
            'labels' => array(
                'name' => 'Jobs BaoProd',
                'singular_name' => 'Job BaoProd',
            ),
            'public' => true,
            'show_ui' => false,
            'supports' => array('title', 'editor', 'custom-fields'),
        ));
        
        // Schedule sync cron
        if (!wp_next_scheduled('baoprod_sync_jobs')) {
            wp_schedule_event(time(), 'hourly', 'baoprod_sync_jobs');
        }
        add_action('baoprod_sync_jobs', array($this, 'sync_jobs'));
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('baoprod-jobs', plugin_dir_url(__FILE__) . 'js/baoprod-jobs.js', array('jquery'), '1.0.0', true);
        wp_localize_script('baoprod-jobs', 'baoprod_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('baoprod_nonce'),
        ));
    }
    
    /**
     * Shortcode pour afficher la liste des jobs
     * Usage: [baoprod_jobs category="tech" limit="10"]
     */
    public function jobs_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => 10,
            'category' => '',
            'location' => '',
            'type' => '',
            'featured_only' => false,
        ), $atts);
        
        $jobs = $this->get_jobs($atts);
        
        if (empty($jobs)) {
            return '<p>Aucune offre d\'emploi disponible pour le moment.</p>';
        }
        
        ob_start();
        ?>
        <div class="baoprod-jobs-list">
            <?php foreach ($jobs as $job): ?>
            <div class="baoprod-job-card" data-job-id="<?php echo esc_attr($job->id); ?>">
                <?php if ($job->is_featured): ?>
                <div class="job-featured-badge">Emploi en vedette</div>
                <?php endif; ?>
                
                <h3 class="job-title"><?php echo esc_html($job->title); ?></h3>
                
                <div class="job-meta">
                    <span class="job-company"><?php echo esc_html($job->employer_name ?: 'Entreprise confidentielle'); ?></span>
                    <?php if ($job->location): ?>
                    <span class="job-location">📍 <?php echo esc_html($job->location); ?></span>
                    <?php endif; ?>
                    <span class="job-type"><?php echo esc_html($job->type_in_french); ?></span>
                </div>
                
                <?php if ($job->formatted_salary): ?>
                <div class="job-salary">💰 <?php echo esc_html($job->formatted_salary); ?></div>
                <?php endif; ?>
                
                <div class="job-description">
                    <?php echo wp_trim_words(strip_tags($job->description), 30); ?>
                </div>
                
                <div class="job-actions">
                    <a href="?job_id=<?php echo $job->id; ?>" class="btn btn-primary">Voir l'offre</a>
                    <button class="btn btn-secondary apply-btn" data-job-id="<?php echo $job->id; ?>">
                        Postuler
                    </button>
                </div>
                
                <div class="job-date">
                    Publiée il y a <?php echo $job->days_since_published; ?> jour(s)
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <style>
        .baoprod-jobs-list { display: grid; gap: 20px; }
        .baoprod-job-card { 
            border: 1px solid #ddd; 
            padding: 20px; 
            border-radius: 8px; 
            position: relative;
        }
        .job-featured-badge { 
            position: absolute; 
            top: 10px; 
            right: 10px; 
            background: #ff6b35; 
            color: white; 
            padding: 5px 10px; 
            border-radius: 4px; 
            font-size: 12px; 
        }
        .job-title { margin-top: 0; color: #333; }
        .job-meta { margin: 10px 0; color: #666; }
        .job-meta span { margin-right: 15px; }
        .job-salary { color: #28a745; font-weight: bold; margin: 10px 0; }
        .job-description { margin: 15px 0; line-height: 1.6; }
        .job-actions { margin: 15px 0; }
        .job-actions .btn { 
            padding: 8px 16px; 
            margin-right: 10px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
        }
        .btn-primary { background: #007cba; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .job-date { font-size: 14px; color: #999; }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Shortcode pour le formulaire de candidature
     * Usage: [baoprod_job_form job_id="123"]
     */
    public function application_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'job_id' => get_query_var('job_id', ''),
        ), $atts);
        
        if (empty($atts['job_id'])) {
            return '<p>ID du poste manquant.</p>';
        }
        
        $job = $this->get_job($atts['job_id']);
        if (!$job) {
            return '<p>Poste non trouvé.</p>';
        }
        
        ob_start();
        ?>
        <div class="baoprod-application-form">
            <h3>Postuler pour : <?php echo esc_html($job->title); ?></h3>
            
            <form id="job-application-form" enctype="multipart/form-data">
                <input type="hidden" name="job_id" value="<?php echo esc_attr($job->id); ?>">
                <input type="hidden" name="action" value="submit_job_application">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('baoprod_nonce'); ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">Prénom *</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Nom *</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                
                <div class="form-group">
                    <label for="cv_file">CV (PDF, DOC, DOCX - Max 5MB)</label>
                    <input type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx">
                </div>
                
                <div class="form-group">
                    <label for="cover_letter">Lettre de motivation</label>
                    <textarea id="cover_letter" name="cover_letter" rows="6" placeholder="Expliquez pourquoi vous êtes le candidat idéal..."></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="expected_salary">Salaire souhaité (XOF)</label>
                        <input type="number" id="expected_salary" name="expected_salary" min="0">
                    </div>
                    <div class="form-group">
                        <label for="available_start_date">Date de disponibilité</label>
                        <input type="date" id="available_start_date" name="available_start_date" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Envoyer ma candidature</button>
            </form>
            
            <div id="application-result" style="display: none;"></div>
        </div>
        
        <style>
        .baoprod-application-form { max-width: 600px; margin: 20px 0; }
        .form-row { display: flex; gap: 20px; }
        .form-row .form-group { flex: 1; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea, .form-group select { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            font-size: 14px; 
        }
        .form-group textarea { resize: vertical; }
        #application-result.success { color: #28a745; background: #d4edda; padding: 15px; border-radius: 4px; }
        #application-result.error { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 4px; }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#job-application-form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                formData.append('source', 'wordpress');
                formData.append('source_url', window.location.href);
                
                $.ajax({
                    url: baoprod_ajax.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('button[type="submit"]').text('Envoi en cours...').prop('disabled', true);
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#application-result')
                                .removeClass('error').addClass('success')
                                .html('<strong>Candidature envoyée !</strong> Nous vous contacterons bientôt.')
                                .show();
                            $('#job-application-form')[0].reset();
                        } else {
                            $('#application-result')
                                .removeClass('success').addClass('error')
                                .html('<strong>Erreur :</strong> ' + response.data)
                                .show();
                        }
                    },
                    error: function() {
                        $('#application-result')
                            .removeClass('success').addClass('error')
                            .html('<strong>Erreur :</strong> Une erreur est survenue. Veuillez réessayer.')
                            .show();
                    },
                    complete: function() {
                        $('button[type="submit"]').text('Envoyer ma candidature').prop('disabled', false);
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * AJAX handler for job application submission
     */
    public function submit_application() {
        if (!wp_verify_nonce($_POST['nonce'], 'baoprod_nonce')) {
            wp_die('Nonce verification failed');
        }
        
        $job_id = intval($_POST['job_id']);
        $application_data = array(
            'tenant_id' => $this->tenant_id,
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'cover_letter' => sanitize_textarea_field($_POST['cover_letter']),
            'expected_salary' => intval($_POST['expected_salary']),
            'available_start_date' => sanitize_text_field($_POST['available_start_date']),
            'source' => 'wordpress',
            'source_url' => sanitize_url($_POST['source_url']),
        );
        
        // Handle file upload
        if (!empty($_FILES['cv_file']['tmp_name'])) {
            $upload = wp_handle_upload($_FILES['cv_file'], array('test_form' => false));
            if (isset($upload['url'])) {
                $application_data['cv_url'] = $upload['url'];
            }
        }
        
        $response = $this->api_call('POST', "/public/jobs/{$job_id}/apply", $application_data);
        
        if ($response && $response['success']) {
            wp_send_json_success('Application submitted successfully');
        } else {
            $error_msg = isset($response['message']) ? $response['message'] : 'Failed to submit application';
            wp_send_json_error($error_msg);
        }
    }
    
    /**
     * Get jobs from BaoProd API
     */
    private function get_jobs($filters = array()) {
        $params = array_merge(array(
            'tenant_id' => $this->tenant_id,
            'per_page' => 20,
        ), $filters);
        
        $response = $this->api_call('GET', '/public/jobs', $params);
        return $response && $response['success'] ? $response['data'] : array();
    }
    
    /**
     * Get single job from BaoProd API
     */
    private function get_job($job_id) {
        $params = array('tenant_id' => $this->tenant_id);
        $response = $this->api_call('GET', "/public/jobs/{$job_id}", $params);
        return $response && $response['success'] ? $response['data'] : null;
    }
    
    /**
     * Sync jobs from BaoProd API to WordPress
     */
    public function sync_jobs() {
        $jobs = $this->get_jobs(array('per_page' => 100));
        
        foreach ($jobs as $job) {
            $existing_post = get_posts(array(
                'post_type' => 'baoprod_job',
                'meta_key' => 'baoprod_job_id',
                'meta_value' => $job->id,
                'posts_per_page' => 1,
            ));
            
            $post_data = array(
                'post_title' => $job->title,
                'post_content' => $job->description,
                'post_status' => 'publish',
                'post_type' => 'baoprod_job',
            );
            
            if ($existing_post) {
                $post_data['ID'] = $existing_post[0]->ID;
                wp_update_post($post_data);
            } else {
                $post_id = wp_insert_post($post_data);
                update_post_meta($post_id, 'baoprod_job_id', $job->id);
            }
            
            // Update job meta
            if (isset($post_id) || isset($post_data['ID'])) {
                $meta_post_id = isset($post_id) ? $post_id : $post_data['ID'];
                update_post_meta($meta_post_id, 'baoprod_job_data', $job);
            }
        }
    }
    
    /**
     * Make API call to BaoProd
     */
    private function api_call($method, $endpoint, $data = array()) {
        $url = rtrim($this->api_url, '/') . $endpoint;
        
        $args = array(
            'method' => $method,
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-API-KEY' => $this->api_key,
            ),
            'timeout' => 30,
        );
        
        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        } elseif ($method === 'POST' && !empty($data)) {
            $args['body'] = json_encode($data);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            error_log('BaoProd API Error: ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);
        
        return $decoded;
    }
    
    /**
     * Admin menu
     */
    public function admin_menu() {
        add_options_page(
            'BaoProd JobBoard Settings',
            'BaoProd Jobs',
            'manage_options',
            'baoprod-jobboard',
            array($this, 'admin_page')
        );
    }
    
    public function admin_init() {
        register_setting('baoprod_settings', 'baoprod_api_url');
        register_setting('baoprod_settings', 'baoprod_api_key');
        register_setting('baoprod_settings', 'baoprod_tenant_id');
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>BaoProd JobBoard Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('baoprod_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">API URL</th>
                        <td>
                            <input type="url" name="baoprod_api_url" value="<?php echo esc_attr($this->api_url); ?>" class="regular-text" />
                            <p class="description">URL de l'API BaoProd (ex: https://workforce.baoprod.com/api)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">API Key</th>
                        <td>
                            <input type="text" name="baoprod_api_key" value="<?php echo esc_attr($this->api_key); ?>" class="regular-text" />
                            <p class="description">Clé API fournie par BaoProd</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Tenant ID</th>
                        <td>
                            <input type="number" name="baoprod_tenant_id" value="<?php echo esc_attr($this->tenant_id); ?>" min="1" />
                            <p class="description">ID de votre organisation dans BaoProd</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            
            <h2>Shortcodes Disponibles</h2>
            <ul>
                <li><code>[baoprod_jobs]</code> - Affiche la liste des offres d'emploi</li>
                <li><code>[baoprod_jobs category="tech" limit="5"]</code> - Avec filtres</li>
                <li><code>[baoprod_job_form job_id="123"]</code> - Formulaire de candidature</li>
            </ul>
            
            <h2>Synchronisation</h2>
            <p>Les emplois sont synchronisés automatiquement toutes les heures.</p>
            <a href="<?php echo wp_nonce_url(admin_url('options-general.php?page=baoprod-jobboard&sync=1'), 'baoprod_sync'); ?>" class="button">Synchroniser maintenant</a>
            
            <?php if (isset($_GET['sync']) && wp_verify_nonce($_GET['_wpnonce'], 'baoprod_sync')): ?>
                <?php $this->sync_jobs(); ?>
                <div class="notice notice-success"><p>Synchronisation effectuée !</p></div>
            <?php endif; ?>
        </div>
        <?php
    }
}

// Initialize the plugin
new BaoProdJobBoardSync();