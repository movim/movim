var SpaceRooms = {
    init: function () {
        var items = document.querySelectorAll('ul#spacerooms_widget > li > ul.list > li');

        var i = 0;

        while (i < items.length) {
            if (items[i].dataset.jid != null) {
                var li = items[i];

                li.onclick = function (e) {
                    Chat.getRoom(this.dataset.jid);
                }

                if (MovimUtils.isMobile()) {
                    let touchTimer;
                    let moveX = 0;
                    let moveY = 0;

                    li.ontouchstart = function (event) {
                        touchTimer = setTimeout(() => {
                            touchTimer = null;

                            if (moveX == 0 && moveY == 0) {
                                SpaceRooms_ajaxAskEdit(this.dataset.jid);
                            }
                        }, 500);
                    }

                    li.ontouchmove = function (event) {
                        moveX += Math.abs(event.targetTouches[0].pageX);
                        moveY += Math.abs(event.targetTouches[0].pageY);
                    }

                    li.ontouchend = function (event) {
                        clearTimeout(touchTimer);
                        moveX = moveY = 0;
                    }
                }
            }

            i++;
        }
    }
}