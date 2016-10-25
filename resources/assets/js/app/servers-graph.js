var ServersGraph;
module.exports = ServersGraph = function (root) {
    this.init(root);
};

ServersGraph.prototype = {
    init: function (root) {
        this.$root = root;
    }
};