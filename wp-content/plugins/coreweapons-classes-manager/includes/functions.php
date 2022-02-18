<?php

function getChildrenOfUser($user_id)
{
    $post_status = ['wc-active'];

    if (WP_DEBUG) {
        $post_status = ['wc-processing', 'wc-active'];
    }

    // $customerOrders = wc_get_orders([
    $customerOrders = WCS_Gifting::get_gifted_subscriptions([
        'customer_id' => $user_id,
        'post_status' => $post_status,
    ]);

    // Entries that must not appear
    // foo7 : foo7@test.com : 534
    // foo10 : foo10@test.com : 535
    // f0011 : foo11@test.com : 536

    $children = array_map(function ($order) {
        $targetKey = '_recipient_user';
        $metaData = $order->get_meta_data();

        if (count($metaData)) {
            // Loop over meta_data objects
            for ($i = 0; $i < count($metaData); $i++) {
                $data = $metaData[$i]->get_data();

                if ($data['key'] == $targetKey) {
                    $user = get_user_by('id', $data['value']);
                    return $user;
                }
            }
        }
    }, $customerOrders);

    $children = _get_unique_children($children);
    $children = _filter_members_with_membership($children, 'core-weapons-course-membership');

    return $children;
}

function getSubjectSubscriptionProducts()
{
    $subscriptions = wc_get_products([
        'type' => 'subscription',
        'category' => 'subjects',

        // We use ASC ordering by date here because our products are required to show `Both` as the last option
        // Since the `Both` product is the lateest product in the three, we flip the default order for the required result
        'orderby' => 'date',
        'order' => 'ASC',
    ]);

    $subjects = array_map(function ($subscription) {
        return [
            'text' => $subscription->get_name(),
            'value' => $subscription->get_id(),
        ];
    }, $subscriptions);

    return $subjects;
}

function filter_children_with_subscription($children, $product_id)
{
    $filtered_children = [];

    foreach ($children as $child) {
        $has_sub = user_has_subscription($child->get('ID'), $product_id);
        if (!$has_sub) $filtered_children[] = $child;
    }

    return $filtered_children;
}

function user_has_subscription($user_id, $product_id)
{
    $post_status = ['wc-active'];

    if (WP_DEBUG) {
        $post_status = ['wc-processing', 'wc-active'];
    }

    return wcs_user_has_subscription($user_id, $product_id, 'wc-active', $post_status);
}

function _get_unique_children($children)
{
    $child_ids = array_values(array_unique(array_map(function ($child) {
        return $child->get('ID');
    }, $children)));


    $filtered_children = [];

    for ($i = 0; $i < count($child_ids); $i++) {
        for ($j = 0; $j < count($children); $j++) {
            if ($children[$j]->get('ID') == $child_ids[$i]) {
                if (!isset($filtered_children[$children[$j]->get('ID')])) {
                    $filtered_children[$children[$j]->get('ID')] = $children[$j];
                }
            }
        }
    }

    $filtered_children = array_values($filtered_children);

    return $filtered_children;
}

function _filter_members_with_membership($users, $membership_id)
{
    $filtered_users = array_filter($users, function ($user) use ($membership_id) {
        return !(wc_memberships_is_user_member($user->get('ID'), $membership_id));
    });

    $filtered_users = array_values($filtered_users);

    return $filtered_users;
}
