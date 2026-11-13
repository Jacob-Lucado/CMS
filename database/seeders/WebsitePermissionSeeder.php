<?php

namespace Database\Seeders;

use App\Models\Miscellaneous\WebsitePermission;
use App\Support\PermissionRanks;
use Illuminate\Database\Seeder;

class WebsitePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'permission' => 'bypass_vpn',
                'min_rank' => '6',
                'description' => 'Min rank to bypass vpn blocker check',
            ],
            [
                'permission' => 'housekeeping_access',
                'min_rank' => '7',
                'description' => 'Minimum required rank to access housekeeping',
            ],
            [
                'permission' => 'delete_article_comments',
                'min_rank' => '7',
                'description' => 'Minimum required rank to delete article comments without being the author',
            ],
            [
                'permission' => 'manage_website_tickets',
                'min_rank' => '7',
                'description' => 'Minimum required rank to view and reply to others tickets',
            ],
            [
                'permission' => 'delete_website_tickets',
                'min_rank' => '7',
                'description' => 'Minimum required rank to delete others tickets',
            ],
            [
                'permission' => 'delete_website_ticket_replies',
                'min_rank' => '7',
                'description' => 'Minimum required rank to delete replies on a ticket',
            ],
            [
                'permission' => 'generate_logo',
                'min_rank' => '7',
                'description' => 'Minimum required rank to use the logo generator',
            ],
            [
                'permission' => 'min_staff_rank',
                'min_rank' => '5',
                'description' => 'Minimum rank to moderate the social feed and receive post reports',
            ],
            [
                'permission' => 'min_home_edit_rank',
                'min_rank' => '7',
                'description' => 'Minimum rank to edit any user\'s home',
            ],
        ];

        foreach ($permissions as $permission) {
            WebsitePermission::firstOrCreate(['permission' => $permission['permission']], [
                'permission' => $permission['permission'],
                'min_rank' => PermissionRanks::scale((int) $permission['min_rank']),
                'description' => $permission['description'],
            ]);
        }
    }
}
