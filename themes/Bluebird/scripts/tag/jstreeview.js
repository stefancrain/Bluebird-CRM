// Generated by CoffeeScript 1.6.3
var Autocomplete, Node, Tree, View, _treeUtils;

window.jstree["views"] = {
  exec: function(instance) {
    console.log("exec");
    return this.view = new View(instance);
  },
  done: function(instance) {
    var a, b, trees, v, _ref;
    trees = {};
    _ref = instance.treeNames;
    for (a in _ref) {
      v = _ref[a];
      b = _treeUtils.selectByTree(instance.autocomplete, a);
      trees[a] = new Tree(b, a);
    }
    this.view.trees = trees;
    this.view.init();
    return console.log("done");
  },
  view: {}
};

View = (function() {
  View.property("trees", {
    get: function() {
      return this._trees;
    },
    set: function(a) {
      return this._trees = a;
    }
  });

  View.prototype.selectors = {
    tagBox: "",
    container: "",
    containerClass: "",
    initHolder: "",
    byHeightWidth: "",
    dropdown: "",
    defaultTree: "",
    activeTree: "",
    isFiltered: false,
    data: "data",
    idedKeys: ["container", "data"],
    addPrefix: ["dropdown", "data"]
  };

  View.prototype.menuSelectors = {
    menu: "menu",
    top: "top",
    tabs: "tabs",
    bottom: "bottom",
    autocomplete: "autocomplete",
    settings: "settings",
    addPrefix: ["menu", "tabs", "top", "bottom", "autocomplete", "settings"]
  };

  View.prototype.tokenHolder = {
    box: "tokenHolder",
    options: "options",
    body: "tokenBody",
    resize: "resize",
    left: "left",
    addPrefix: ["box", "options", "body", "resize", "left"]
  };

  View.prototype.settings = {
    tall: true,
    wide: true,
    edit: false,
    tagging: false,
    print: true
  };

  View.prototype.entity_id = 0;

  View.prototype.defaultPrefix = "JSTree";

  View.prototype.prefixes = [];

  View.prototype.defaultTree = 0;

  function View(instance) {
    this.instance = instance;
    this.writeContainers();
  }

  View.prototype.writeContainers = function() {
    this.formatPageElements();
    this.createSelectors();
    return this.addClassesToElement();
  };

  View.prototype.addClassesToElement = function() {
    this.cj_selectors.initHolder.html("<div class='" + this.selectors.tagBox + "'></div>");
    this.cj_selectors.initHolder.prepend(this.menuHtml(this.menuSelectors));
    this.cj_selectors.initHolder.append(this.dataHolderHtml());
    this.cj_selectors.initHolder.append(this.tokenHolderHtml(this.tokenHolder));
    return this.cj_selectors.initHolder.removeClass(this.selectors.initHolder).attr("id", this.selectors.container).addClass(this.selectors.containerClass);
  };

  View.prototype.formatPageElements = function() {
    var displaySettings, pageElements, v, _i, _len, _ref;
    pageElements = this.instance.get('pageElements');
    displaySettings = this.instance.get('displaySettings');
    this.selectors.container = pageElements.wrapper.shift();
    this.selectors.containerClass = pageElements.wrapper.join(" ");
    this.selectors.tagBox = pageElements.tagHolder.join(" ");
    this.menuSelectors.tabs = pageElements.tabLocation;
    this.menuSelectors.autocomplete = pageElements.autocomplete;
    this.selectors.dropdown = pageElements.tagDropdown;
    this.selectors.initHolder = pageElements.init;
    this.settings = displaySettings;
    this.settingCollection = ["settings", "menuSelectors", "tokenHolder", "selectors"];
    _ref = pageElements.tagHolder;
    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
      v = _ref[_i];
      this.prefixes.push(v);
    }
    this.joinPrefix();
    return this.selectors.byHeightWidth = this.setByHeightWidth();
  };

  View.prototype.joinPrefix = function() {
    var a, i, k, name, o, v, _i, _len, _ref, _results;
    _ref = this.settingCollection;
    _results = [];
    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
      v = _ref[_i];
      _results.push((function() {
        var _j, _len1, _ref1, _ref2, _results1;
        _ref1 = this["" + v];
        _results1 = [];
        for (k in _ref1) {
          o = _ref1[k];
          if (typeof o !== "string" || o.length === 0) {
            continue;
          }
          if (this["" + v].idedKeys != null) {
            if (this["" + v].idedKeys.indexOf(k) >= 0) {
              if (this["" + v].addPrefix != null) {
                if (this["" + v].addPrefix.indexOf(k) >= 0) {
                  this["" + v][k] = "" + this.prefixes[0] + "-" + o;
                  this["" + v].addPrefix.splice(this["" + v].addPrefix.indexOf(k), 1);
                }
              }
            }
          }
          if (this["" + v].addPrefix != null) {
            if (this["" + v].addPrefix.indexOf(k) >= 0) {
              name = "";
              _ref2 = this.prefixes;
              for (i = _j = 0, _len1 = _ref2.length; _j < _len1; i = ++_j) {
                a = _ref2[i];
                name += "" + a + "-" + o;
                if (this.prefixes.length - 1 > i) {
                  name += " ";
                }
              }
              _results1.push(this["" + v][k] = name);
            } else {
              _results1.push(void 0);
            }
          } else {
            _results1.push(void 0);
          }
        }
        return _results1;
      }).call(this));
    }
    return _results;
  };

  View.prototype.createSelectors = function() {
    var v, _i, _len, _ref, _results;
    _ref = this.settingCollection;
    _results = [];
    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
      v = _ref[_i];
      _results.push(this.createCJfromObj(this[v], v));
    }
    return _results;
  };

  View.prototype.createCJfromObj = function(obj, name) {
    var cjed, k, selectorType, v;
    cjed = {};
    for (k in obj) {
      v = obj[k];
      if (typeof v !== "string" || v.length === 0) {
        continue;
      }
      selectorType = ".";
      if (obj.idedKeys != null) {
        if (obj["idedKeys"].indexOf(k) >= 0) {
          selectorType = "#";
        }
      }
      cjed[k] = cj("" + selectorType + (cj.trim(v).replace(/\ /g, ".")));
    }
    return this["cj_" + name] = cjed;
  };

  View.prototype.setByHeightWidth = function() {
    var ret;
    ret = "";
    if (!this.settings.wide) {
      ret += "narrow ";
    }
    if (!this.settings.tall) {
      ret += "short";
    }
    return ret;
  };

  View.prototype.menuHtml = function(name) {
    return "      <div class='" + name.menu + "'>       <div class='" + name.top + "'>        <div class='" + name.tabs + "'></div>        <div class='" + name.settings + "'></div>       </div>       <div class='" + name.bottom + "'>        <div class='" + name.autocomplete + "'>         <input type='text' id='JSTree-ac'>        </div>        <div class='" + name.settings + "'></div>       </div>      </div>    ";
  };

  View.prototype.tokenHolderHtml = function(name) {
    return "        <div class='" + name.box + "'>         <div class='" + name.resize + "'></div>         <div class='" + name.body + "'>          <div class='" + name.left + "'></div>          <div class='" + name.options + "'></div>         </div>        </div>      ";
  };

  View.prototype.dataHolderHtml = function() {
    return "<div id='JSTree-data' style='display:none'></div>";
  };

  View.prototype.init = function() {
    var ac, k, tabName, v, _ref;
    this.createSelectors();
    _ref = this.instance.treeNames;
    for (k in _ref) {
      v = _ref[k];
      tabName = this.createTreeTabs(v);
    }
    this.setActiveTree(this.settings.defaultTree);
    return ac = new Autocomplete(this.instance, this);
  };

  View.prototype.setActiveTree = function(id) {
    var tabName;
    tabName = this.getTabNameFromId(id, true);
    cj(".JSTree-tabs .tab-" + tabName).addClass("active");
    return cj(".JSTree .top-" + id).addClass("active");
  };

  View.prototype.createTreeTabs = function(tabName, isHidden) {
    var output, style, tabClass;
    if (isHidden == null) {
      isHidden = false;
    }
    if (isHidden) {
      style = "style='display:none'";
    } else {
      style = "";
    }
    tabClass = (_utils.hyphenize(tabName)).toLowerCase();
    output = "<div class='tab-" + tabClass + "' " + style + ">" + tabName + "</div>";
    return this.cj_menuSelectors.tabs.append(output);
  };

  View.prototype.getTabNameFromId = function(id, hyphenize) {
    var treeNames;
    if (hyphenize == null) {
      hyphenize = false;
    }
    treeNames = this.instance.treeNames;
    if (!hyphenize) {
      return treeNames[id];
    }
    return _utils.hyphenize(treeNames[id]).toLowerCase();
  };

  View.prototype.getIdFromTabName = function(tabName) {};

  View.prototype.buildFilteredList = function(a, b, c) {
    var buildList, checkAgainst, d, e, k, m, n, o, x, y, _ref;
    checkAgainst = {};
    for (m in a) {
      n = a[m];
      checkAgainst[m] = [];
      for (x in n) {
        y = n[x];
        checkAgainst[m].push(parseFloat(y.id));
      }
    }
    console.log(checkAgainst);
    buildList = {};
    for (d in checkAgainst) {
      e = checkAgainst[d];
      buildList[d] = [];
      _ref = this.instance.autocomplete;
      for (k in _ref) {
        o = _ref[k];
        if (e.indexOf(parseFloat(o.id)) >= 0) {
          buildList[d].push(o);
        }
      }
    }
    return console.log(buildList);
  };

  return View;

})();

Autocomplete = (function() {
  function Autocomplete(instance, view) {
    var cjac, params, searchmonger,
      _this = this;
    this.instance = instance;
    this.view = view;
    this.pageElements = this.instance.get('pageElements');
    this.dataSettings = this.instance.get('dataSettings');
    if (this.cjTagBox == null) {
      this.cjTagBox = cj("." + (this.pageElements.tagHolder.join(".")));
    }
    cj("#JSTree-data").data({
      "autocomplete": this.instance.autocomplete
    });
    params = {
      jqDataReference: "#JSTree-data",
      hintText: "Type in a partial or complete name of an tag or keyword.",
      theme: "JSTree"
    };
    cjac = cj("#JSTree-ac");
    searchmonger = cjac.tagACInput("init", params);
    cjac.on("keydown", (function(event) {
      return _this.filterKeydownEvents(event, searchmonger, cjac);
    }));
    cjac.on("keyup", (function(event) {
      var keyCode;
      return keyCode = bbUtils.keyCode(event);
    }));
  }

  Autocomplete.prototype.filterKeydownEvents = function(event, searchmonger, cjac) {
    var keyCode, name;
    keyCode = bbUtils.keyCode(event);
    switch (keyCode.type) {
      case "directional":
        return this.moveDropdown(keyCode.type);
      case "letters":
      case "delete":
      case "math":
      case "punctuation":
      case "number":
        if (keyCode.type !== "delete") {
          name = keyCode.name;
        } else {
          name = "";
        }
        return this.execSearch(event, searchmonger, cjac, name);
      default:
        return false;
    }
  };

  Autocomplete.prototype.moveDropdown = function() {};

  Autocomplete.prototype.execSearch = function(event, searchmonger, cjac, lastLetter) {
    var openLeg, term,
      _this = this;
    term = cjac.val() + lastLetter;
    if (term.length >= 3) {
      openLeg = new OpenLeg;
      openLeg.query({
        "term": term
      }, function(results) {});
      return searchmonger.exec(event, function(terms) {
        var foundTags, hcounts, hits, k, set, tags, v;
        if ((terms != null) && !cj.isEmptyObject(terms)) {
          tags = _this.sortSearchedTags(terms.tags);
          hits = _this.separateHits(tags);
          hcounts = 0;
          foundTags = [];
          for (k in hits) {
            v = hits[k];
            hcounts += v;
            foundTags.push(parseFloat(k));
          }
          for (set in _this.view.trees) {
            console.log(set);
            if (foundTags.indexOf(parseFloat(set)) < 0) {
              hits[set] = 0;
              tags[set] = [];
            }
          }
          _this.view.buildFilteredList(tags, terms.term.toLowerCase(), hits);
        }
        if ((terms != null) && cj.isEmptyObject(terms)) {
          tags = {};
          return _this.view.buildFilteredList(tags, terms.term.toLowerCase(), {
            291: 0,
            296: 0
          });
        }
      });
    }
  };

  Autocomplete.prototype.separateHits = function(terms, results) {
    var hits, k, v;
    hits = {};
    for (k in terms) {
      v = terms[k];
      hits[k] = v.length;
    }
    return hits;
  };

  Autocomplete.prototype.addPositionReminderText = function(cjlocation) {
    var positionText;
    positionText = "            <dl class='top-292 tagContainer' style='display:none'>              <div class='position-box-text-reminder'>                Type in a Bill Number or Name for Results              </div>            </dl>          ";
    return cjlocation.append(positionText);
  };

  Autocomplete.prototype.sortSearchedTags = function(tags) {
    var list;
    list = {};
    cj.each(tags, function(i, el) {
      var obj;
      if (list[el.type] == null) {
        list[el.type] = [];
      }
      obj = {
        id: el.id,
        name: el.name
      };
      return list[el.type].push(obj);
    });
    return list;
  };

  return Autocomplete;

})();

Tree = (function() {
  Tree.prototype.domList = {};

  Tree.prototype.nodeList = {};

  Tree.prototype.tabName = "";

  function Tree(tagList, tagId, filter) {
    this.tagList = tagList;
    this.tagId = tagId;
    this.filter = filter != null ? filter : false;
    this.buildTree();
  }

  Tree.prototype.buildTree = function() {
    var filter;
    if (this.filter) {
      filter = "filtered";
    } else {
      filter = "";
    }
    this.domList = cj();
    this.domList = this.domList.add("<div class='top-" + this.tagId + " " + filter + " tagContainer'></div>");
    return this.iterate(this.tagList);
  };

  Tree.prototype.iterate = function(ary) {
    var cjTagList, kNode, node, _i, _len;
    cjTagList = cj(this.domList);
    for (_i = 0, _len = ary.length; _i < _len; _i++) {
      node = ary[_i];
      this.nodeList[node.id] = kNode = new Node(node);
      if (node.parent === this.tagId) {
        cjTagList.append(kNode.html);
      } else {
        cjTagList.find("dl#tagDropdown_" + kNode.parent).append(kNode.html);
      }
    }
    return cjTagList.appendTo(".JSTree");
  };

  return Tree;

})();

_treeUtils = {
  selectByParent: function(list, parent) {
    var b, childList, _i, _len;
    childList = [];
    for (_i = 0, _len = list.length; _i < _len; _i++) {
      b = list[_i];
      if (b.parent === parent) {
        childList.push(b);
      }
    }
    return childList;
  },
  selectByTree: function(list, tree) {
    var b, treeList, _i, _len;
    treeList = [];
    for (_i = 0, _len = list.length; _i < _len; _i++) {
      b = list[_i];
      if (b.type === tree) {
        treeList.push(b);
      }
    }
    return treeList;
  }
};

Node = (function() {
  function Node(node) {
    this.data = node;
    this.parent = node.parent;
    this.hasDesc = "";
    this.description = node.description;
    this.descLength(node.description);
    this.id = node.id;
    this.children = node.children;
    this.name = node.name;
    this.html = this.html(node);
    return this;
  }

  Node.prototype.descLength = function(description) {
    this.description = description;
    if (this.description != null) {
      if (description.length > 0) {
        this.hasDesc = "description";
      }
      if (this.description.length > 0 && this.description.length <= 80) {
        this.hasDesc += " shortdescription";
      }
      if (this.description.length > 160) {
        this.hasDesc = "longdescription";
      }
      if (this.description.length > 80) {
        this.description = _utils.textWrap(this.description, 80);
      }
      return console.log;
    }
  };

  Node.prototype.html = function(node) {
    var html, treeButton;
    if (this.parent > 0) {
      treeButton = "treeButton";
    } else {
      treeButton = "";
    }
    if (parseFloat(node.is_reserved) !== 0) {
      this.reserved = true;
    } else {
      this.reserved = false;
    }
    html = "<dt class='lv-" + node.level + " " + this.hasDesc + "' id='tagLabel_" + node.id + "' data-tagid='" + node.id + "' data-name='" + node.name + "' data-parentid='" + node.parent + "'>";
    html += "              <div class='tag'>                <div class='ddControl " + treeButton + "'></div>                <span class='name'>" + node.name + "</span>            ";
    if (this.hasDesc.length > 0) {
      html += "                <div class='description'>" + this.description + "</div>            ";
    }
    html += "              </div>              <div class='transparancyBox type-" + node.type + "'></div>            ";
    html += "</dt>";
    html += "              <dl class='lv-" + node.level + "' id='tagDropdown_" + node.id + "' data-name='" + node.name + "'></dl>            ";
    return html;
  };

  return Node;

})();
