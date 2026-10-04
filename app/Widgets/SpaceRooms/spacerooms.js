var SpaceRooms = {
    draggingDirectory: false,

    init: function () {
        document.querySelectorAll('ul#spacerooms_widget > li > ul.list > li:not(.subheader)[data-jid]').forEach(li => {
            const jid = li.dataset.jid;

            li.onclick = () => Chat.getRoom(jid);

            if (MovimUtils.isMobile()) {
                let touchTimer;
                let isMoving = false;

                li.ontouchstart = () => {
                    isMoving = false;
                    touchTimer = setTimeout(() => {
                        if (!isMoving) SpaceRooms_ajaxAskEdit(jid);
                    }, 500);
                };

                li.ontouchmove = () => { isMoving = true; };
                li.ontouchend = () => clearTimeout(touchTimer);
            }
        });
    },
    editable: function () {
        var items = document.querySelectorAll('ul#spacerooms_widget > li');

        items.forEach(item => {
            item.addEventListener('dragstart', () => item.classList.add('dragging'));
            item.addEventListener("dragend", () => {
                let dropped = document.querySelector('li.dropping');

                item.classList.remove("dragging");
                items.forEach(subheader => subheader.classList.remove('dropping'));

                if (dropped) {
                    dropped.after(item);

                    const [server, node] = MovimUtils.urlParts().params;

                    if (SpaceRooms.draggingDirectory == true) {
                        if (dropped.classList.contains('subheader')) {
                            SpaceRooms_ajaxSortDirectories(
                                server,
                                node,
                                Array.from(
                                    document.querySelectorAll('ul#spacerooms_widget > li.subheader'),
                                    node => node.dataset.id
                                )
                            );
                        }
                    } else {
                        let firstItem = item;
                        let directoryId = null;

                        // Find the first item and see if its a directory

                        while (firstItem.previousElementSibling && !firstItem.previousElementSibling.classList.contains('subheader')) {
                            firstItem = firstItem.previousElementSibling;
                        }

                        if (firstItem.previousElementSibling?.classList.contains('subheader')) {
                            directoryId = firstItem.previousElementSibling.dataset.id;
                        }

                        // Then we iterate on the items of the directory
                        if (firstItem) {
                            let currentItem = firstItem;
                            let weight = 0;

                            // Extract URL parameters once to avoid repeating the function calls inside a loop
                            while (currentItem && !currentItem.classList.contains('subheader')) {
                                SpaceRooms_ajaxSetHierarchy(
                                    server,
                                    node,
                                    currentItem.dataset.jid,
                                    weight++,
                                    directoryId
                                );

                                currentItem = currentItem.nextElementSibling;
                            }
                        }
                    }
                }

                SpaceRooms.draggingDirectory = false;
            });
            item.addEventListener('dragover', (event) => {
                event.preventDefault();
                let elements = document.querySelectorAll('ul#spacerooms_widget > li:not(.dragging)');

                let siblings = [...elements];

                let nextSibling = siblings.reduce((closest, child) => {
                    const box = child.getBoundingClientRect();
                    const offset = event.clientY - box.top - box.height / 2;
                    if (offset < 0 && offset > closest.offset) {
                        return { offset: offset, element: child };
                    } else {
                        return closest;
                    }
                }, { offset: Number.NEGATIVE_INFINITY }).element;
                elements.forEach(subheader => {
                    if (!subheader.isSameNode(nextSibling)) {
                        subheader.classList.remove('dropping')
                    }
                });

                if (nextSibling) {
                    if ((SpaceRooms.draggingDirectory && nextSibling.classList.contains('subheader'))
                        || (SpaceRooms.draggingDirectory == false)) {
                        nextSibling.classList.add('dropping');
                    }
                }
            })
        });

        var directories = document.querySelectorAll('ul#spacerooms_widget > li.subheader');

        directories.forEach(directory => {
            directory.addEventListener('dragstart', () => SpaceRooms.draggingDirectory = true);
        });
    }
}