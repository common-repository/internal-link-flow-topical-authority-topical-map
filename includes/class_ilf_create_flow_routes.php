<?php

class Class_Ilf_Create_Flow_Routes
{


    private $table_flow;
    private $db;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->table_flow = $this->db->prefix . 'internal_link_flow';
        add_action('rest_api_init', [$this, 'create_rest_routes']);
    }

    public function create_rest_routes()
    {
        // mevcut kayıtlar
        register_rest_route('tailf/v1', '/list', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'flow_list'],
            'permission_callback' => [$this, 'save_settings_permission']
        ]);
        // konular
        register_rest_route('tailf/v1', '/posts', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'post_list'],
            'permission_callback' => [$this, 'save_settings_permission']
        ]);
        // Yeni
        register_rest_route('tailf/v1', '/create', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'flow_create'],
            'permission_callback' => [$this, 'save_settings_permission']
        ]);
        // Güncelle
        register_rest_route('tailf/v1', '/update', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'flow_create'],
            'permission_callback' => [$this, 'save_settings_permission']
        ]);
        //sil
        register_rest_route('tailf/v1', '/delete/(?P<id>\d+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'flow_delete'],
            'permission_callback' => [$this, 'save_settings_permission']
        ]);

        // Düzenleme ve görüntüleme
        register_rest_route('tailf/v1', '/edit/(?P<id>\d+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'flow_edit'],
            'permission_callback' => [$this, 'save_settings_permission']
        ]);

        // 1 flow kayıt getir.
        register_rest_route('tailf/v1', '/flow/(?P<id>\d+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_flow'],
            'permission_callback' => [$this, 'save_settings_permission']
        ]);
        register_rest_route('tailf/v1', '/test', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'flow_test'],
            'permission_callback' => [$this, 'save_settings_permission']
        ]);
    }

    public function flow_test()
    {
        $response = [
            'flow_id' => "1"
        ];
        return rest_ensure_response($response);
    }

    public function post_list($req)
    {
        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'any',
            'post_status' => array('publish', 'pending', 'draft', 'future', 'private')
        );
        $the_query = new WP_Query($args);
        $post_data = [];
        foreach ($the_query->posts as $p) {
            $post_data[] = (object)[
                'id' => $p->ID,
                'title' => $p->post_title,
                'author' => get_the_author_meta('display_name', $p->post_author),
                'link' => get_permalink($p->ID),
                'date' => $p->post_date,
                'status' => $p->post_status,
                'type' => $p->post_type,

            ];
        }
        return rest_ensure_response($post_data);
        //return get_posts($args);
    }

    public function flow_list()
    {
        $list = $this->db->get_results($this->db->prepare("select id,name,user,node_count,updated_at from $this->table_flow order by updated_at desc"));
        return rest_ensure_response($list);
    }

    public function get_flow($req)
    {
        $fid = sanitize_text_field($req['id']);
        $flow = $this->db->get_row("select id,name,user,node_count,updated_at from $this->table_flow where id=$fid");
        return rest_ensure_response($flow);
    }

    public function flow_edit($req)
    {
        $flow = $this->db->get_row($this->db->prepare("select * from $this->table_flow where id=%d",
            $req['id']
        ));
        return rest_ensure_response($flow);
    }

    public function flow_create($req)
    {
        $current_user = wp_get_current_user();
        $fid = sanitize_text_field($req['fid']);
        $name = sanitize_text_field($req['name']);
        $userName = $current_user->display_name;
        $nodeCount = sanitize_text_field($req['node_count']);
        $nodes = $req['nodes'];
        $edges = $req['edges'];
        $viewport = $req['viewport'];
        $created_at = date_create('now')->format('Y-m-d H:i:s');
        $updated_at = date_create('now')->format('Y-m-d H:i:s');
        if ($fid == 0) {
            $this->db->insert(
                $this->table_flow,
                array(
                    'user' => $userName,
                    'name' => $name,
                    'node_count' => $nodeCount,
                    'nodes' => $nodes,
                    'edges' => $edges,
                    'viewport' => $viewport,
                    'created_at' => $created_at,
                    'updated_at' => $updated_at
                )
            );
            $fid = $this->db->insert_id;
        } else {
            $this->db->update(
                $this->table_flow,
                array(
                    'user' => $userName,
                    'name' => $name,
                    'node_count' => $nodeCount,
                    'nodes' => $nodes,
                    'edges' => $edges,
                    'viewport' => $viewport,
                    'created_at' => $created_at,
                    'updated_at' => $updated_at),
                array('id' => $fid),
                array('%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s'),
                array('%d')
            );

        }

        $response = [
            'flow_id' => $fid
        ];
        return rest_ensure_response($response);
    }

    public function flow_delete($req)
    {
        $fid = sanitize_text_field($req['id']);

        $c = $this->db->delete($this->table_flow, array('id' => $fid), array('%d'));
        $response = [
            'status' => $c,
            'id' => $fid
        ];
        return rest_ensure_response($response);
    }

    public function save_settings_permission()
    {
        return current_user_can('administrator');
    }

}

new Class_Ilf_Create_Flow_Routes();