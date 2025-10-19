var SendTo = {
    contacts: {},
    init: function () {
        SendTo.contacts = {};

        document.querySelectorAll('#sendto_share_contacts > li[data-jid]').forEach(li => {
            var store = new ChatOmemoStorage();

            store.getContactState(li.dataset.jid).then(enabled => {
                if (enabled) li.classList.add('disabled');
            });
        });
    },

    shareArticle: function (link) {
        SendTo_ajaxShareArticle(link, typeof navigator.share == 'function');
    },

    toggleSend: function (contact) {
        if (contact.classList.contains('sharing')) {
            contact.classList.remove('sharing');
            delete SendTo.contacts[contact.dataset.jid];
        } else {
            contact.classList.add('sharing');
            SendTo.contacts[contact.dataset.jid] = Boolean(contact.dataset.muc);
        }

        var button = document.getElementById('sendto_button');
        var contactsCount = Object.keys(SendTo.contacts).length;

        document.getElementById('sendto_counter').innerText = contactsCount;
        button.classList.remove('disabled');
        if (contactsCount == 0) {
            button.classList.add('disabled');
        }
    },

    sendToContacts: function(uri) {
        SendTo_ajaxSend(SendTo.contacts, uri);
        SendTo.contacts = {};
    },

    shareOs(object) {
        navigator.share(object);
    },
}
