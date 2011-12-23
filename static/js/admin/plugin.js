/**
 * ModernWeb
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.modernweb.pl/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@modernweb.pl so we can send you a copy immediately.
 *
 * @category    Pimcore
 * @package     Plugin_Poll
 * @author      Rafał Gałka <rafal@modernweb.pl>
 * @copyright   Copyright (c) 2007-2011 ModernWeb (http://www.modernweb.pl)
 * @license     http://www.modernweb.pl/license/new-bsd     New BSD License
 */
pimcore.registerNS("pimcore.plugin.poll");

pimcore.plugin.poll = Class.create(pimcore.plugin.admin, {

    getClassName: function() {
        return "pimcore.plugin.poll";
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker){
        // backward pimcore compatibility
        if(pimcore.plugin.admin.prototype.getMenu == undefined) {
            var toolbar = Ext.getCmp("pimcore_panel_toolbar");
            toolbar.items.items[1].menu.add(this.getMenu());
        }
    },

    addPoll: function() {
        Ext.MessageBox.prompt(
            t('create_poll'),
            t('please_enter_the_poll_question'),
            this.addPollCreate.bind(this)
        );
    },

    addPollCreate: function(button, value){
        if (button == "ok") {
            Ext.Ajax.request({
                url: "/plugin/Poll/admin/add/",
                params: {
                    question: value
                },
                success: this.addPollComplete.bind(this)
            });
        }
    },

    addPollComplete: function(response) {
        try {
            var rdata = Ext.decode(response.responseText);
            if (rdata && rdata.success) {
                if (pimcore.globalmanager.exists("poll_list")) {
                    pimcore.globalmanager.get("poll_list").reload();
                }
                this.openPollTab(rdata.id);
            } else {
                pimcore.helpers.showNotification(
                    t("error"),
                    t("error_creating_poll"),
                    "error"
                );
            }
        } catch (e) {
            pimcore.helpers.showNotification(
                t("error"),
                t("error_creating_poll"),
                "error"
            );
        }
    },

    openPollTab: function(id) {
        if (!pimcore.globalmanager.exists("poll_" + id)) {
            pimcore.globalmanager.add("poll_" + id, new pimcore.plugin.poll.poll(id));
        } else {
            pimcore.globalmanager.get("poll_" + id).activate();
        }
    },

    openListTab: function() {
        if (!pimcore.globalmanager.exists("poll_list")) {
            pimcore.globalmanager.add("poll_list", new pimcore.plugin.poll.list(this));
        } else {
            pimcore.globalmanager.get("poll_list").activate();
        }
    },

    getMenu: function(){
        return new Ext.menu.Item({
            text: t("polls"),
            iconCls: "plugin_poll_icon",
            hideOnClick: false,
            menu: [{
                text: t("create_poll"),
                iconCls: "plugin_poll_icon_add",
                handler: this.addPoll.bind(this)
            },{
                text: t("manage_polls"),
                iconCls: "plugin_poll_icon_edit",
                handler: this.openListTab.bind(this)
            }]
        });
    }
});

var poll = new pimcore.plugin.poll();
