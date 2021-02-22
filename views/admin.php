<div class="wrap">
    <h1>Okta Integration Options</h1>
    <form class="okta-integration-options" method="POST">
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th class="row">
                        <label for="force_auth"><strong>Force Authentication</strong></label>
                    </th>
                    <td>
                        <label for="force_auth">
                            <input type="checkbox" name="force_auth" id="force_auth" value="1"<?php if ( $this->force_auth ) { ?> checked<?php } ?>>
                            Force
                        </label>
                        <p class="description" id="force-auth-description">
                            This setting will override the <em>Pages Forced to Authenticate</em> option. It will ensure only those who have authenticated through Okta can view the site.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th class="row">
                        <label for="saml_login">SAML Login URL</label>
                    </th>
                    <td>
                        <input class="regular-text" type="text" name="saml_login" id="saml_login" value="<?= $this->saml_login; ?>">
                    </td>
                </tr>
                <tr>
                    <th class="row">
                        <label for="domains[0]">Referral Domains</label>
                    </th>
                    <td>
                        <div>
                            <?php
                                if ( count( $this->domains ) === 0 ) {
                            ?>
                                <div class="domain" style="display:flex;align-items: center;">
                                    <input class="regular-text" type="text" name="domains[]" id="domains[0]">
                                    <a class="js-add-domain" href="#" style="color:inherit;text-decoration:none;margin:.5rem;"><i class="dashicons dashicons-plus"></i></a>
                                    <a class="js-remove-domain" href="#" style="color:inherit;text-decoration:none;margin:.5rem;"><i class="dashicons dashicons-no"></i></a>
                                </div>
                            <?php
                                } else {
                                    for  ( $i = 0; $i < count( $this->domains ); $i++ ) {
                                        $domain = $this->domains[$i];
                            ?>
                                        <div class="domain" style="display:flex;align-items: center;">
                                            <input class="regular-text" type="text" name="domains[]" id="domains[<?= $i; ?>]" value="<?= $domain; ?>">
                                            <a class="js-add-domain" href="#" style="color:inherit;text-decoration:none;margin:.5rem;"><i class="dashicons dashicons-plus"></i></a>
                                            <a class="js-remove-domain" href="#" style="color:inherit;text-decoration:none;margin:.5rem;"><i class="dashicons dashicons-no"></i></a>
                                        </div>
                            <?php
                                    }
                                }
                            ?>
                        </div>
                        <p class="description" id="domains-description">
                            Add as many domains as you need. Please include the protocol (http://, https://, etc.);
                        </p>
                    </td>
                </tr>
                <tr>
                    <th class="row">
                        <label>Pages Forced to Authenticate</label>
                    </th>
                    <td>
                        <table class="wp-list-table widefat striped table-view-list">
                            <thead>
                                <tr>
                                    <th class="manage-column column-page_name column-primary sortable desc" id="page_name" scope="col" colspan="100%">
                                        <a href="#">
                                            <span>Post or Page Name</span>
                                            <span class="sorting-indicator"></span>
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    if ( count( $this->pages ) === 0 ) {
                                ?>
                                        <tr class="no-items">
                                            <td>No items found.</td>
                                        </tr>
                                <?php
                                    } else {
                                        foreach ( $this->pages as $page ) {
                                            $thePost = get_post( $page );

                                            if ( $thePost ) {
                                ?>
                                                <tr>
                                                    <td>
                                                        <a class="row-title" href="/wp-admin/post.php?post=<?= $thePost->ID; ?>&action=edit">
                                                            <?= $thePost->post_title; ?>
                                                        </a>
                                                    </td>
                                                </tr>
                                <?php
                                            }
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>
                        <p class="description" id="force-pages-description">
                            These pages will be readable only to those who have authenticated through Okta. You can set these individually directly on the post or page.
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
    </form>
</div>
<script type="text/template" id="js-domain-template">
    <input class="regular-text" type="text" name="domains[]" id="domains[]">
    <a class="js-add-domain" href="#" style="color:inherit;text-decoration:none;margin:.5rem;"><i class="dashicons dashicons-plus"></i></a>
    <a class="js-remove-domain" href="#" style="color:inherit;text-decoration:none;margin:.5rem;"><i class="dashicons dashicons-no"></i></a>
</script>
