CREATE TABLE `badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `banned_ip_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creator_id` bigint(20) NOT NULL,
  `reason` longtext NOT NULL,
  `ip_address` longtext NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `expires_at` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_banned_ip_addresses_creator_id` (`creator_id`),
  CONSTRAINT `fk_banned_ip_addresses_players_creator_id` FOREIGN KEY (`creator_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `catalog_club_offers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` longtext DEFAULT NULL,
  `duration_days` int(11) NOT NULL,
  `cost_credits` int(11) NOT NULL,
  `cost_points` int(11) NOT NULL,
  `cost_points_type` int(11) NOT NULL,
  `is_vip` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `catalog_front_page_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` longtext DEFAULT NULL,
  `image` longtext DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `product_name` longtext DEFAULT NULL,
  `catalog_page_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_catalog_front_page_items_catalog_page_id` (`catalog_page_id`),
  CONSTRAINT `fk_catalog_front_page_items_catalog_pages_catalog_page_id` FOREIGN KEY (`catalog_page_id`) REFERENCES `catalog_pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `catalog_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` longtext DEFAULT NULL,
  `cost_credits` int(11) NOT NULL,
  `cost_points` int(11) NOT NULL,
  `cost_points_type` int(11) NOT NULL,
  `requires_club_membership` tinyint(1) NOT NULL,
  `meta_data` longtext DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `stack_limit` int(11) NOT NULL,
  `sell_limit` int(11) NOT NULL,
  `catalog_page_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_catalog_items_catalog_page_id` (`catalog_page_id`),
  CONSTRAINT `fk_catalog_items_catalog_pages_catalog_page_id` FOREIGN KEY (`catalog_page_id`) REFERENCES `catalog_pages` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `catalog_item_furniture_item` (
  `catalog_items_id` int(11) NOT NULL,
  `furniture_items_id` int(11) NOT NULL,
  PRIMARY KEY (`catalog_items_id`,`furniture_items_id`),
  KEY `ix_catalog_item_furniture_item_furniture_items_id` (`furniture_items_id`),
  CONSTRAINT `fk_catalog_item_furniture_item_catalog_items_catalog_items_id` FOREIGN KEY (`catalog_items_id`) REFERENCES `catalog_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_catalog_item_furniture_item_furniture_items_furniture_items_` FOREIGN KEY (`furniture_items_id`) REFERENCES `furniture_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `catalog_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` longtext DEFAULT NULL,
  `caption` longtext DEFAULT NULL,
  `layout` longtext DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `catalog_page_id` int(11) DEFAULT NULL,
  `order_id` int(11) NOT NULL,
  `icon_id` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `visible` tinyint(1) NOT NULL,
  `images_json` longtext NOT NULL,
  `texts_json` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_catalog_pages_catalog_page_id` (`catalog_page_id`),
  CONSTRAINT `fk_catalog_pages_catalog_pages_catalog_page_id` FOREIGN KEY (`catalog_page_id`) REFERENCES `catalog_pages` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `furniture_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` longtext NOT NULL,
  `asset_name` longtext NOT NULL,
  `type` longtext NOT NULL,
  `asset_id` int(11) NOT NULL,
  `tile_span_x` int(11) NOT NULL,
  `tile_span_y` int(11) NOT NULL,
  `stack_height` double NOT NULL,
  `can_stack` tinyint(1) NOT NULL,
  `can_walk` tinyint(1) NOT NULL,
  `can_sit` tinyint(1) NOT NULL,
  `can_lay` tinyint(1) NOT NULL,
  `can_recycle` tinyint(1) NOT NULL,
  `can_trade` tinyint(1) NOT NULL,
  `can_marketplace_sell` tinyint(1) NOT NULL,
  `can_inventory_stack` tinyint(1) NOT NULL,
  `can_gift` tinyint(1) NOT NULL,
  `interaction_type` longtext DEFAULT NULL,
  `interaction_modes` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `furniture_item_hand_item` (
  `furniture_items_id` int(11) NOT NULL,
  `hand_items_id` int(11) NOT NULL,
  PRIMARY KEY (`furniture_items_id`,`hand_items_id`),
  KEY `ix_furniture_item_hand_item_hand_items_id` (`hand_items_id`),
  CONSTRAINT `fk_furniture_item_hand_item_furniture_items_furniture_items_id` FOREIGN KEY (`furniture_items_id`) REFERENCES `furniture_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_furniture_item_hand_item_hand_item_hand_items_id` FOREIGN KEY (`hand_items_id`) REFERENCES `hand_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) NOT NULL,
  `name` longtext NOT NULL,
  `description` longtext NOT NULL,
  `room_id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_groups_room_id` (`room_id`),
  CONSTRAINT `fk_groups_rooms_room_id` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `group_player` (
  `group_id` int(11) NOT NULL,
  `player_id` bigint(20) NOT NULL,
  PRIMARY KEY (`group_id`,`player_id`),
  KEY `ix_group_player_player_id` (`player_id`),
  CONSTRAINT `fk_group_player_groups_group_id` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_group_player_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `hand_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `navigator_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` longtext NOT NULL,
  `code_name` longtext NOT NULL,
  `order_id` int(11) NOT NULL,
  `tab_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_navigator_categories_tab_id` (`tab_id`),
  CONSTRAINT `fk_navigator_categories_navigator_tabs_tab_id` FOREIGN KEY (`tab_id`) REFERENCES `navigator_tabs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `navigator_tabs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `oauth_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `secret` varchar(256) NOT NULL,
  `domain` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `players` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(60) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_avatar_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) NOT NULL,
  `figure_code` varchar(200) NOT NULL,
  `motto` varchar(50) DEFAULT NULL,
  `gender` longtext NOT NULL DEFAULT 'M',
  `chat_bubble_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_player_avatar_data_player_id` (`player_id`),
  CONSTRAINT `fk_player_avatar_data_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `slot` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_badges_badge_id` (`badge_id`),
  KEY `ix_player_badges_player_id` (`player_id`),
  CONSTRAINT `fk_player_badges_badges_badge_id` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_player_badges_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_bans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creator_id` bigint(20) NOT NULL,
  `player_id` bigint(20) NOT NULL,
  `reason` longtext NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `expires_at` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_bans_creator_id` (`creator_id`),
  KEY `ix_player_bans_player_id` (`player_id`),
  CONSTRAINT `fk_player_bans_players_creator_id` FOREIGN KEY (`creator_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_player_bans_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_bots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `username` longtext NOT NULL,
  `figure_code` longtext NOT NULL,
  `motto` longtext NOT NULL,
  `gender` longtext NOT NULL,
  `chat_bubble_id` int(11) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_bots_player_id` (`player_id`),
  CONSTRAINT `fk_player_bots_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) NOT NULL,
  `home_room_id` int(11) DEFAULT NULL,
  `credit_balance` int(11) NOT NULL,
  `pixel_balance` int(11) NOT NULL,
  `seasonal_balance` int(11) NOT NULL,
  `gotw_points` int(11) NOT NULL,
  `respect_points` int(11) NOT NULL DEFAULT 15,
  `respect_points_pet` int(11) NOT NULL DEFAULT 15,
  `achievement_score` int(11) NOT NULL DEFAULT 15,
  `allow_friend_requests` tinyint(1) NOT NULL DEFAULT 1,
  `is_online` tinyint(1) NOT NULL DEFAULT 0,
  `last_online` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_player_data_player_id` (`player_id`),
  CONSTRAINT `fk_player_data_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_friendships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `origin_player_id` bigint(20) NOT NULL,
  `target_player_id` bigint(20) NOT NULL,
  `status` int(11) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_friendships_origin_player_id` (`origin_player_id`),
  KEY `ix_player_friendships_target_player_id` (`target_player_id`),
  CONSTRAINT `fk_player_friendships_players_origin_player_id` FOREIGN KEY (`origin_player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_player_friendships_players_target_player_id` FOREIGN KEY (`target_player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_furniture_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) NOT NULL,
  `furniture_item_id` int(11) NOT NULL,
  `limited_data` longtext NOT NULL DEFAULT '',
  `meta_data` longtext NOT NULL DEFAULT '',
  `created_at` datetime(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_furniture_items_furniture_item_id` (`furniture_item_id`),
  KEY `ix_player_furniture_items_player_id` (`player_id`),
  CONSTRAINT `fk_player_furniture_items_furniture_items_furniture_item_id` FOREIGN KEY (`furniture_item_id`) REFERENCES `furniture_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_player_furniture_items_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_furniture_item_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `child_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_furniture_item_links_child_id` (`child_id`),
  KEY `ix_player_furniture_item_links_parent_id` (`parent_id`),
  CONSTRAINT `fk_player_furniture_item_links_player_furniture_items_child_id` FOREIGN KEY (`child_id`) REFERENCES `player_furniture_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_player_furniture_item_links_player_furniture_items_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `player_furniture_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_furniture_item_placement_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_furniture_item_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `position_x` int(11) NOT NULL,
  `position_y` int(11) NOT NULL,
  `position_z` double NOT NULL,
  `wall_position` longtext DEFAULT NULL,
  `direction` int(11) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_player_furniture_item_placement_data_player_furniture_item_id` (`player_furniture_item_id`),
  KEY `ix_player_furniture_item_placement_data_room_id` (`room_id`),
  CONSTRAINT `fk_player_furniture_item_placement_data_player_furniture_items_` FOREIGN KEY (`player_furniture_item_id`) REFERENCES `player_furniture_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_player_furniture_item_placement_data_rooms_room_id` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_furniture_item_wired_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_furniture_item_placement_data_id` int(11) NOT NULL,
  `message` longtext NOT NULL,
  `delay` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_player_furniture_item_wired_data_player_furniture_item_place` (`player_furniture_item_placement_data_id`),
  CONSTRAINT `fk_player_furniture_item_wired_data_player_furniture_item_place` FOREIGN KEY (`player_furniture_item_placement_data_id`) REFERENCES `player_furniture_item_placement_data` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_furniture_item_wired_data_items` (
  `player_furniture_item_placement_data_id` int(11) NOT NULL,
  `player_furniture_item_wired_data_id` int(11) NOT NULL,
  PRIMARY KEY (`player_furniture_item_placement_data_id`,`player_furniture_item_wired_data_id`),
  KEY `ix_player_furniture_item_wired_data_items_player_furniture_item` (`player_furniture_item_wired_data_id`),
  CONSTRAINT `fk_player_furniture_item_wired_data_items_player_furniture_item` FOREIGN KEY (`player_furniture_item_placement_data_id`) REFERENCES `player_furniture_item_placement_data` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_player_furniture_item_wired_data_items_player_furniture_item1` FOREIGN KEY (`player_furniture_item_wired_data_id`) REFERENCES `player_furniture_item_wired_data` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_game_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) NOT NULL,
  `system_volume` int(11) NOT NULL DEFAULT 100,
  `furniture_volume` int(11) NOT NULL DEFAULT 100,
  `trax_volume` int(11) NOT NULL DEFAULT 100,
  `prefer_old_chat` tinyint(1) NOT NULL DEFAULT 0,
  `block_room_invites` tinyint(1) NOT NULL DEFAULT 0,
  `block_camera_follow` tinyint(1) NOT NULL DEFAULT 0,
  `ui_flags` int(11) NOT NULL DEFAULT 1,
  `show_notifications` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_player_game_settings_player_id` (`player_id`),
  CONSTRAINT `fk_player_game_settings_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_ignores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) NOT NULL,
  `target_player_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_ignores_player_id` (`player_id`),
  CONSTRAINT `fk_player_ignores_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `origin_player_id` bigint(20) NOT NULL,
  `target_player_id` bigint(20) NOT NULL,
  `message` varchar(250) DEFAULT NULL,
  `created_at` datetime(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_messages_origin_player_id` (`origin_player_id`),
  KEY `ix_player_messages_target_player_id` (`target_player_id`),
  CONSTRAINT `fk_player_messages_players_origin_player_id` FOREIGN KEY (`origin_player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_player_messages_players_target_player_id` FOREIGN KEY (`target_player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_navigator_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) NOT NULL,
  `window_x` int(11) NOT NULL DEFAULT 50,
  `window_y` int(11) NOT NULL DEFAULT 50,
  `window_width` int(11) NOT NULL DEFAULT 435,
  `window_height` int(11) NOT NULL DEFAULT 535,
  `open_searches` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_player_navigator_settings_player_id` (`player_id`),
  CONSTRAINT `fk_player_navigator_settings_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_relationships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `origin_player_id` bigint(20) NOT NULL,
  `target_player_id` bigint(20) NOT NULL,
  `type_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_relationships_origin_player_id` (`origin_player_id`),
  KEY `ix_player_relationships_target_player_id` (`target_player_id`),
  CONSTRAINT `fk_player_relationships_players_origin_player_id` FOREIGN KEY (`origin_player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_player_relationships_players_target_player_id` FOREIGN KEY (`target_player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_relationship_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_respects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `origin_player_id` bigint(20) NOT NULL,
  `target_player_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_respects_origin_player_id` (`origin_player_id`),
  KEY `ix_player_respects_target_player_id` (`target_player_id`),
  CONSTRAINT `fk_player_respects_players_origin_player_id` FOREIGN KEY (`origin_player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_player_respects_players_target_player_id` FOREIGN KEY (`target_player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_role` (
  `role_id` int(11) NOT NULL,
  `player_id` bigint(20) NOT NULL,
  PRIMARY KEY (`role_id`,`player_id`),
  KEY `ix_player_role_player_id` (`player_id`),
  CONSTRAINT `fk_player_role_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_player_role_roles_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_room_bans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) NOT NULL,
  `room_id` int(11) NOT NULL,
  `expires_at` datetime(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_room_bans_player_id` (`player_id`),
  KEY `ix_player_room_bans_room_id` (`room_id`),
  CONSTRAINT `fk_player_room_bans_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_player_room_bans_rooms_room_id` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_room_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) NOT NULL,
  `room_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_room_likes_player_id` (`player_id`),
  KEY `ix_player_room_likes_room_id` (`room_id`),
  CONSTRAINT `fk_player_room_likes_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_player_room_likes_rooms_room_id` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_room_visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) NOT NULL,
  `room_id` int(11) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_room_visits_player_id` (`player_id`),
  CONSTRAINT `fk_player_room_visits_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_saved_searches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `search` longtext DEFAULT NULL,
  `filter` longtext DEFAULT NULL,
  `player_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_saved_searches_player_id` (`player_id`),
  CONSTRAINT `fk_player_saved_searches_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_sso_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) NOT NULL,
  `token` varchar(200) DEFAULT NULL,
  `created_at` datetime(6) NOT NULL,
  `expires_at` datetime(6) NOT NULL,
  `used_at` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_sso_tokens_player_id` (`player_id`),
  CONSTRAINT `fk_player_sso_tokens_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `expires_at` datetime(6) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_subscriptions_player_id` (`player_id`),
  KEY `ix_player_subscriptions_subscription_id` (`subscription_id`),
  CONSTRAINT `fk_player_subscriptions_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_player_subscriptions_subscriptions_subscription_id` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` longtext DEFAULT NULL,
  `player_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_tags_player_id` (`player_id`),
  CONSTRAINT `fk_player_tags_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_wardrobe_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slot_id` int(11) NOT NULL,
  `figure_code` longtext DEFAULT NULL,
  `gender` int(11) NOT NULL,
  `player_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_wardrobe_items_player_id` (`player_id`),
  CONSTRAINT `fk_player_wardrobe_items_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `player_website_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `initial_ip` longtext NOT NULL,
  `last_ip` longtext NOT NULL,
  `last_login` datetime(6) NOT NULL,
  `player_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_player_website_data_player_id` (`player_id`),
  CONSTRAINT `fk_player_website_data_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `roles_permissions` (
  `permission_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `ix_roles_permissions_role_id` (`role_id`),
  CONSTRAINT `fk_roles_permissions_permissions_permission_id` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_roles_permissions_roles_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` longtext NOT NULL,
  `layout_id` int(11) NOT NULL,
  `owner_id` bigint(20) NOT NULL,
  `max_users_allowed` int(11) NOT NULL,
  `description` longtext NOT NULL,
  `is_muted` tinyint(1) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_rooms_layout_id` (`layout_id`),
  KEY `ix_rooms_owner_id` (`owner_id`),
  CONSTRAINT `fk_rooms_players_owner_id` FOREIGN KEY (`owner_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rooms_room_layouts_layout_id` FOREIGN KEY (`layout_id`) REFERENCES `room_layouts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `room_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caption` longtext DEFAULT NULL,
  `is_visible` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `room_chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `player_id` bigint(20) NOT NULL,
  `message` longtext DEFAULT NULL,
  `chat_bubble_id` int(11) NOT NULL,
  `emotion_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_room_chat_messages_player_id` (`player_id`),
  KEY `ix_room_chat_messages_room_id` (`room_id`),
  CONSTRAINT `fk_room_chat_messages_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_room_chat_messages_rooms_room_id` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `room_chat_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `chat_type` int(11) NOT NULL DEFAULT 0,
  `chat_weight` int(11) NOT NULL DEFAULT 1,
  `chat_speed` int(11) NOT NULL DEFAULT 1,
  `chat_distance` int(11) NOT NULL DEFAULT 50,
  `chat_protection` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_room_chat_settings_room_id` (`room_id`),
  CONSTRAINT `fk_room_chat_settings_rooms_room_id` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `room_dimmer_presets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` bigint(20) NOT NULL,
  `preset_id` int(11) NOT NULL,
  `background_only` tinyint(1) NOT NULL,
  `color` longtext NOT NULL,
  `intensity` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `room_dimmer_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `preset_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_room_dimmer_settings_room_id` (`room_id`),
  CONSTRAINT `fk_room_dimmer_settings_rooms_room_id` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `room_layouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` longtext DEFAULT NULL,
  `heightmap` longtext DEFAULT NULL,
  `door_x` int(11) NOT NULL,
  `door_y` int(11) NOT NULL,
  `door_direction` int(11) NOT NULL,
  `requires_club_membership` tinyint(1) NOT NULL,
  `extra_data` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `room_paint_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `floor_paint` longtext NOT NULL DEFAULT '0.0',
  `wall_paint` longtext NOT NULL DEFAULT '0.0',
  `landscape_paint` longtext NOT NULL DEFAULT '0.0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_room_paint_settings_room_id` (`room_id`),
  CONSTRAINT `fk_room_paint_settings_rooms_room_id` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `room_player_rights` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `player_id` bigint(20) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_room_player_rights_room_id` (`room_id`),
  CONSTRAINT `fk_room_player_rights_rooms_room_id` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `room_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `walk_diagonal` tinyint(1) NOT NULL DEFAULT 1,
  `access_type` int(11) NOT NULL DEFAULT 0,
  `password` longtext DEFAULT NULL,
  `who_can_mute` int(11) NOT NULL DEFAULT 0,
  `who_can_kick` int(11) NOT NULL DEFAULT 0,
  `who_can_ban` int(11) NOT NULL DEFAULT 0,
  `allow_pets` tinyint(1) NOT NULL DEFAULT 1,
  `can_pets_eat` tinyint(1) NOT NULL DEFAULT 1,
  `hide_walls` tinyint(1) NOT NULL DEFAULT 0,
  `wall_thickness` int(11) NOT NULL DEFAULT 0,
  `floor_thickness` int(11) NOT NULL DEFAULT 0,
  `can_users_overlap` tinyint(1) NOT NULL DEFAULT 0,
  `trade_option` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix_room_settings_room_id` (`room_id`),
  CONSTRAINT `fk_room_settings_rooms_room_id` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `room_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` longtext NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_room_tags_room_id` (`room_id`),
  CONSTRAINT `fk_room_tags_rooms_room_id` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `server_locale_texts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(120) NOT NULL,
  `text` varchar(300) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `server_periodic_currency_rewards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` longtext DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `interval_seconds` int(11) NOT NULL,
  `skip_idle` tinyint(1) NOT NULL,
  `skip_hotel_view` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `server_periodic_currency_reward_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` bigint(20) NOT NULL,
  `type` longtext DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_server_periodic_currency_reward_logs_player_id` (`player_id`),
  CONSTRAINT `fk_server_periodic_currency_reward_logs_players_player_id` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `server_player_constants` (
  `max_motto_length` int(11) NOT NULL,
  `min_sso_length` int(11) NOT NULL,
  `max_friendships` int(11) NOT NULL,
  `created_at` datetime(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `server_room_constants` (
  `max_chat_message_length` int(11) NOT NULL,
  `seconds_till_user_idle` int(11) NOT NULL,
  `max_name_length` int(11) NOT NULL,
  `max_description_length` int(11) NOT NULL,
  `max_tag_length` int(11) NOT NULL,
  `wired_max_furniture_selection` int(11) NOT NULL,
  `created_at` datetime(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `server_settings` (
  `player_welcome_message` longtext DEFAULT NULL,
  `fair_currency_rewards` tinyint(1) NOT NULL,
  `created_at` datetime(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Ada seed data
INSERT INTO `permissions` (`id`, `name`) VALUES (1,'moderator'),
(2,'command_shutdown'),
(3,'command_hotel_alert'),
(4,'command_user_info'),
(5,'command_kick'),
(6,'command_kick_all'),
(7,'command_unload'),
(8,'any_room_owner'),
(9,'any_room_rights');
INSERT INTO `roles` (`id`, `name`) VALUES (1,'User'),
(5,'Moderator'),
(6,'Admin');
INSERT INTO `roles_permissions` (`permission_id`, `role_id`) VALUES (1,5),
(3,5),
(4,5),
(5,5),
(6,5),
(7,5),
(8,5),
(9,5),
(1,6),
(2,6),
(3,6),
(4,6),
(5,6),
(6,6),
(7,6),
(8,6),
(9,6);
