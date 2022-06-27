/*globals Jodit */
(() => {
    function init() {
        document.querySelectorAll(".joditeditor textarea").forEach((el) => {
            const config = JSON.parse(el.dataset.config);
            const editor = Jodit.make(el, config);
        });
    }

    init();

    const publicApi = {
        init,
    };
    window.SSJodit = window.SSJodit || {};
    window.SSJodit = Object.assign(window.SSJodit, publicApi);
})();
