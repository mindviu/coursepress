<?php
$plugins = get_option('active_plugins');
$required_plugin = 'wordpress-chat/wordpress-chat.php';

if (in_array($required_plugin, $plugins) || is_plugin_network_active($required_plugin)) {

    class chat_module extends Unit_Module {

        var $name = 'chat_module';
        var $label = 'Live Chat';
        var $description = 'Allows adding chat blocks from WordPress Chat plugin to the unit';
        var $front_save = false;
        var $response_type = '';

        function __construct() {
            $this->on_create();
        }

        function chat_module() {
            $this->__construct();
        }

        function front_main($data) {
            ?>
            <div class="<?php echo $this->name; ?>">
                <h2 class="module_title"><?php echo $data->post_title; ?></h2>
                <div class="module_description"><?php echo $data->post_content; ?></div>
                <?php echo do_shortcode('[chat id="' . $data->ID . '"]'); ?>
            </div>
            <?php
        }

        function admin_main($data) {
            ?>

            <div class="<?php if (empty($data)) { ?>draggable-<?php } ?>module-holder-<?php echo $this->name; ?> module-holder-title" <?php if (empty($data)) { ?>style="display:none;"<?php } ?>>

                <h3 class="module-title sidebar-name">
                    <span class="h3-label"><?php echo $this->label; ?><?php echo (isset($data->post_title) ? ' (' . $data->post_title . ')' : ''); ?></span>
                </h3>

                <div class="module-content">
                    <?php if (isset($data->ID)) { parent::get_module_delete_link($data->ID); }else{ parent::get_module_remove_link();} ?>
                    <input type="hidden" name="<?php echo $this->name; ?>_module_order[]" class="module_order" value="<?php echo (isset($data->module_order) ? $data->module_order : 999); ?>" />
                    <input type="hidden" name="module_type[]" value="<?php echo $this->name; ?>" />
                    <input type="hidden" name="<?php echo $this->name; ?>_id[]" value="<?php echo (isset($data->ID) ? $data->ID : ''); ?>" />
                    <label><?php _e('Title', 'cp'); ?>
                        <input type="text" name="<?php echo $this->name; ?>_title[]" value="<?php echo esc_attr(isset($data->post_title) ? $data->post_title : ''); ?>" />
                    </label>

                    <div class="editor_in_place">
                        <?php
                        $args = array("textarea_name" => $this->name . "_content[]", "textarea_rows" => 5);
                        wp_editor(stripslashes(esc_attr(isset($data->post_content) ? $data->post_content : '')), (esc_attr(isset($data->ID) ? 'editor_' . $data->ID : '')), $args);
                        ?>
                    </div>

                </div>

            </div>

            <?php
        }

        function on_create() {
            $this->save_module_data();
            parent::additional_module_actions();
        }

        function save_module_data() {
            global $wpdb, $last_inserted_unit_id;

            if (isset($_POST['module_type'])) {

                foreach (array_keys($_POST['module_type']) as $module_type => $module_value) {

                    if ($module_value == $this->name) {
                        $data = new stdClass();
                        $data->ID = '';
                        $data->unit_id = '';
                        $data->title = '';
                        $data->excerpt = '';
                        $data->content = '';
                        $data->metas = array();
                        $data->metas['module_type'] = $this->name;
                        $data->post_type = 'module';

                        foreach ($_POST[$this->name . '_id'] as $key => $value) {
                            $data->ID = $_POST[$this->name . '_id'][$key];
                            $data->unit_id = ((isset($_POST['unit_id']) and $_POST['unit'] != '') ? $_POST['unit_id'] : $last_inserted_unit_id);
                            $data->title = $_POST[$this->name . '_title'][$key];
                            $data->content = $_POST[$this->name . '_content'][$key];
                            $data->metas['module_order'] = $_POST[$this->name . '_module_order'][$key];
                            parent::update_module($data);
                        }
                    }
                }
            }
        }

    }

    coursepress_register_module('chat_module', 'chat_module', 'instructors');
}
?>
