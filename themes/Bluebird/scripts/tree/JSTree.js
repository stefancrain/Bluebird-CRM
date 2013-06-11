// Generated by CoffeeScript 1.6.2
(function() {
  var BBTagLabel, View, getTrees, instance, parseTree, tree, treeBehavior, _ref, _ref1, _treeData,
    __slice = [].slice,
    __indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

  tree = {
    startInstance: function(submittedProperties) {
      var initInstance, request,
        _this = this;

      initInstance = new instance();
      this.setProp(submittedProperties, initInstance);
      request = cj.when(getTrees.getRawJSON(initInstance));
      request.done(function(data) {
        getTrees.putRawJSON(data.message, initInstance);
        parseTree.init(initInstance);
        return initInstance.set('ready', true);
      });
      return initInstance;
    },
    setProp: function() {
      var instance, k, properties, v, _i, _ref, _results;

      properties = 2 <= arguments.length ? __slice.call(arguments, 0, _i = arguments.length - 1) : (_i = 0, []), instance = arguments[_i++];
      _ref = properties[0];
      _results = [];
      for (k in _ref) {
        v = _ref[k];
        _results.push(instance.set(k, v));
      }
      return _results;
    }
  };

  treeBehavior = {
    getEntityTags: function() {},
    tagActions: function() {}
  };

  if ((_ref = window.jstree) == null) {
    window.jstree = tree;
  }

  if ((_ref1 = window.CRM) == null) {
    window.CRM = {};
  }

  getTrees = {
    getRawJSON: function(instance) {
      return cj.ajax(instance.get('callAjax'));
    },
    putRawJSON: function(data, instance) {
      return cj.each(data, function(i, tID) {
        var _ref2;

        if (_ref2 = parseFloat(tID.id), __indexOf.call(instance.get('dataSettings').pullSets, _ref2) >= 0) {
          return _treeData.rawData[tID.id] = {
            'name': tID.name,
            'children': tID.children
          };
        }
      });
    }
  };

  parseTree = {
    init: function(instance) {
      var _this = this;

      cj.each(_treeData.rawData, function(id, cID) {
        var tagName, _ref2;

        if (_ref2 = parseFloat(id), __indexOf.call(instance.get('dataSettings').pullSets, _ref2) >= 0) {
          _this.output = '';
          _this.tagLvl = 0;
          _this.setDataType(cID.name);
          _this.autocompleteObj = [];
          _this.treeTop = id;
          tagName = new BBTagLabel(id);
          _this.addTabName(cID.name);
          _this.output += "<dl class='top-" + cID.id + "'>";
          cj.each(cID.children, function(id, tID) {
            return _this.writeOutputData(tID);
          });
          _this.output += "</dl>";
          return _this.writeData();
        }
      });
      return console.log("Loaded Data");
    },
    isItemMarked: function(value, type) {
      if (value(true)) {
        return type;
      } else {
        return '';
      }
    },
    isItemChildless: function(childLength) {
      if (childLength > 0) {
        return 'treeButton';
      } else {
        return '';
      }
    },
    writeOutputData: function(tID, parentTag) {
      var hasChild, tagName,
        _this = this;

      tagName = new BBTagLabel(tID.id);
      this.addAutocompleteEntry(tID.id, tID.name);
      if (tID.children.length > 0) {
        hasChild = true;
      } else {
        hasChild = false;
      }
      this.addDTtag(tagName, tID.name, parentTag, hasChild);
      this.addDLtop(tagName, tID.name);
      if (hasChild) {
        cj.each(tID.children, function(id, cID) {
          return _this.writeOutputData(cID, tID.id);
        });
        return this.addDLbottom();
      } else {
        return this.addDLbottom();
      }
    },
    addTabName: function(name) {
      return _treeData.treeNames.push(name);
    },
    addDLtop: function(tagName, name) {
      return this.output += "<dl class='lv-" + this.tagLvl + "' id='" + (tagName.addDD()) + "' data-name='" + name + "'>";
    },
    addDTtag: function(tagName, name, parentTag, hasChild, except) {
      var treeButton;

      if (!except) {
        this.tagLvl++;
      }
      if (hasChild) {
        treeButton = "treeButton";
      } else {
        treeButton = "";
      }
      if (parentTag == null) {
        parentTag = this.treeTop;
      }
      this.output += "<dt class='lv-" + this.tagLvl + " " + this.tagType + "-" + (tagName.passThru()) + "' id='" + (tagName.add()) + "' data-tagid='" + (tagName.passThru()) + "' data-name='" + name + "' data-parentid='" + parentTag + "'>";
      this.output += "<div class='tag'>";
      this.output += "<div class='ddControl " + treeButton + "'></div>";
      this.output += "<span class='name'>" + name + "</span></div>";
      return this.output += "</dt>";
    },
    addDLbottom: function() {
      this.tagLvl--;
      return this.output += "</dl>";
    },
    setDataType: function(name) {
      switch (name) {
        case "Issue Code":
          return this.tagType = "issueCode";
        case "Positions":
          return this.tagType = "position";
        case "Keywords":
          return this.tagType = "keyword";
        default:
          return this.tagType = "tag";
      }
    },
    addAutocompleteEntry: function(id, name) {
      var tempObj;

      tempObj = {
        "name": name,
        "id": id,
        "type": this.treeTop
      };
      return this.autocompleteObj.push(tempObj);
    },
    writeData: function() {
      _treeData.autocomplete = _treeData.autocomplete.concat(this.autocompleteObj);
      return _treeData.html[this.treeTop] = this.output;
    }
  };

  _treeData = {
    autocomplete: [],
    rawData: {},
    html: {},
    treeNames: []
  };

  instance = (function() {
    function instance() {
      var callAjax, dataSettings, displaySettings, onSave, pageElements, ready,
        _this = this;

      pageElements = {
        init: 'JSTreeInit',
        wrapper: 'JSTreeContainer',
        tagHolder: ['JSTree'],
        messageHandler: ['JSMessages'],
        location: ''
      };
      onSave = false;
      ready = false;
      dataSettings = {
        pullSets: [291, 296],
        contact: 0
      };
      displaySettings = {
        defaultTree: 291,
        mode: 'edit',
        fullSize: true,
        autocomplete: true,
        print: true,
        showActive: true,
        showStubs: false
      };
      callAjax = {
        url: '/civicrm/ajax/tag/tree',
        data: {
          entity_table: 'civicrm_contact',
          entity_id: 0,
          call_uri: window.location.href,
          entity_counts: 0
        },
        dataType: 'json'
      };
      this.get = function(name) {
        var getRet;

        getRet = {};
        if ('pageElements' === name) {
          cj.extend(true, getRet, pageElements);
        }
        if ('onSave' === name) {
          return onSave;
        }
        if ('dataSettings' === name) {
          cj.extend(true, getRet, dataSettings);
        }
        if ('displaySettings' === name) {
          cj.extend(true, getRet, displaySettings);
        }
        if ('callAjax' === name) {
          cj.extend(true, getRet, callAjax);
        }
        if ('ready' === name) {
          return ready;
        }
        return getRet;
      };
      this.set = function(name, obj) {
        if ('pageElements' === name) {
          obj = _this.checkForArray(pageElements, obj);
          cj.extend(true, pageElements, obj);
        }
        if ('onSave' === name) {
          onSave = obj;
        }
        if ('dataSettings' === name) {
          obj = _this.checkForArray(dataSettings, obj);
          cj.extend(true, dataSettings, obj);
        }
        if ('displaySettings' === name) {
          obj = _this.checkForArray(displaySettings, obj);
          cj.extend(true, displaySettings, obj);
        }
        if ('callAjax' === name) {
          obj = _this.checkForArray(callAjax, obj);
          cj.extend(true, callAjax, obj);
        }
        if ('ready' === name) {
          return ready = obj;
        }
      };
      this.getAutocomplete = function() {
        return _treeData.autocomplete;
      };
    }

    instance.prototype.checkForArray = function(propDefault, obj) {
      return cj.each(obj, function(k, def) {
        var a, ar, b, c, i, _i, _j, _len, _len1;

        if (cj.isArray(def) && cj.isArray(propDefault[k])) {
          a = propDefault[k].sort();
          b = def.sort();
          for (i = _i = 0, _len = a.length; _i < _len; i = ++_i) {
            c = a[i];
            if (c !== b[i]) {
              for (_j = 0, _len1 = def.length; _j < _len1; _j++) {
                ar = def[_j];
                propDefault[k].push(ar);
              }
            }
          }
          return obj[k] = propDefault[k];
        }
      });
    };

    return instance;

  })();

  BBTagLabel = (function() {
    function BBTagLabel(tagID) {
      this.tagID = tagID;
    }

    BBTagLabel.prototype.add = function() {
      return "tagLabel_" + this.tagID;
    };

    BBTagLabel.prototype.remove = function() {
      return this.tagID.replace("tagLabel_", "");
    };

    BBTagLabel.prototype.addDD = function() {
      return "tagDropdown_" + this.tagID;
    };

    BBTagLabel.prototype.removeDD = function() {
      return this.tagID.replace("tagDropdown_", "");
    };

    BBTagLabel.prototype.passThru = function() {
      return this.tagID;
    };

    return BBTagLabel;

  })();

  window.jstree.views = {
    createNewView: function(instance) {
      var newView;

      return newView = new View(instance);
    }
  };

  View = (function() {
    function View(instance) {
      this.instance = instance;
      this.writeContainers();
      this.interval = this.setUpdateInterval(1000);
    }

    View.prototype.getData = function() {
      if (this.instance.get('ready') === true) {
        this.killUpdateInterval(this.interval);
        return this.writeTreeFromSource();
      }
    };

    View.prototype.setUpdateInterval = function(timeSet) {
      var callback,
        _this = this;

      callback = function() {
        return _this.getData();
      };
      return setInterval(callback, timeSet);
    };

    View.prototype.killUpdateInterval = function(clearInt) {
      return clearInterval(clearInt);
    };

    View.prototype.writeContainers = function() {
      this.formatPageElements();
      return this.addClassesToElement();
    };

    View.prototype.addClassesToElement = function() {
      this.cjInitHolderId.html("<div class='" + this.addClassHolderString + "'></div>");
      this.addMenuToElement();
      this.addTokenHolderToElement();
      this.addDataHolderToElement();
      return this.cjInitHolderId.removeClass(this.initHolderId).attr("id", this.addIdWrapperString);
    };

    View.prototype.addMenuToElement = function() {
      var menu;

      menu = "      <div class='" + this.menuName.menu + "'>       <div class='" + this.menuName.top + "'>        <div class='" + this.menuName.tabs + "'></div>        <div class='" + this.menuName.settings + "'></div>       </div>       <div class='" + this.menuName.bottom + "'>        <div class='" + this.menuName.autocomplete + "'>         <input type='text' id='JSTree-ac'>        </div>        <div class='" + this.menuName.settings + "'></div>       </div>      </div>    ";
      return this.cjInitHolderId.prepend(menu);
    };

    View.prototype.addDataHolderToElement = function() {
      var dataHolder;

      dataHolder = "<div id='JSTree-data' style='display:none'></div>";
      return this.cjInitHolderId.append(dataHolder);
    };

    View.prototype.addTokenHolderToElement = function() {
      var tokenHolder;

      tokenHolder = "      <div class='" + this.tokenHolder.tokenHolder + "'>       <div class='" + this.tokenHolder.resize + "'></div>       <div class='" + this.tokenHolder.body + "'>        <div class='" + this.tokenHolder.left + "'></div>        <div class='" + this.tokenHolder.options + "'></div>       </div>      </div>    ";
      return this.cjInitHolderId.append(tokenHolder);
    };

    View.prototype.formatPageElements = function() {
      var i, pageElements, selector, _i, _len, _ref2, _ref3;

      pageElements = this.instance.get('pageElements');
      _ref2 = ["", ""], this.tagHolderSelector = _ref2[0], this.tagWrapperSelector = _ref2[1];
      this.menuName = {
        menu: "",
        top: "",
        tabs: "",
        bottom: "",
        autocomplete: "",
        settings: ""
      };
      this.tokenHolder = {
        tokenHolder: "",
        options: "",
        body: "",
        resize: "",
        left: ""
      };
      this.addIdWrapperString = pageElements.wrapper;
      this.addClassHolderString = pageElements.tagHolder;
      this.initHolderId = pageElements.init;
      this.cjInitHolderId = cj("." + this.initHolderId);
      this.addClassHolderString = this.ifisarrayjoin(this.addClassHolderString);
      _ref3 = pageElements.tagHolder;
      for (i = _i = 0, _len = _ref3.length; _i < _len; i = ++_i) {
        selector = _ref3[i];
        selector = selector.replace(" ", "-");
        this.menuName = this.concatOnObj(this.menuName, selector);
        this.tokenHolder = this.concatOnObj(this.tokenHolder, selector);
        this.tagHolderSelector = this.tagHolderSelector.concat("." + selector);
      }
      return this.tagWrapperSelector = this.tagWrapperSelector.concat("#" + pageElements.wrapper);
    };

    View.prototype.ifisarrayjoin = function(toJoin) {
      if (cj.isArray(toJoin)) {
        return toJoin = toJoin.join(" ");
      }
    };

    View.prototype.concatOnObj = function(obj, selector, classOrId) {
      var k, v;

      if (classOrId == null) {
        classOrId = ".";
      }
      for (k in obj) {
        v = obj[k];
        if (k.substr(0, 3) === "cj_") {
          break;
        }
        if (typeof obj["cj_" + k] === "undefined") {
          obj["cj_" + k] = "";
        }
        obj["cj_" + k] = obj["cj_" + k].concat("" + classOrId + selector + "-" + k);
        obj[k] = obj[k].concat("" + selector + "-" + k + " ");
      }
      return obj;
    };

    View.prototype.getCJQsaves = function() {
      this.cjTagWrapperSelector = cj(this.tagWrapperSelector);
      this.cjTagHolderSelector = cj(this.tagHolderSelector);
      this.cjInstanceSelector = cj(this.tagWrapperSelector.concat(" " + this.tagHolderSelector));
      return this.cjTagMenu = cj(this.menuSelector);
    };

    View.prototype.writeTreeFromSource = function() {
      this.getCJQsaves();
      this.displaySettings = this.instance.get('displaySettings');
      this.writeTabs();
      this.cjInstanceSelector.html(_treeData.html[this.displaySettings.defaultTree]);
      treeBehavior.autoCompleteStart(this.instance);
      return treeBehavior.enableDropdowns();
    };

    View.prototype.writeTabs = function() {
      var a, b, output, _i, _len, _ref2;

      output = "";
      _ref2 = _treeData.treeNames;
      for (_i = 0, _len = _ref2.length; _i < _len; _i++) {
        a = _ref2[_i];
        b = a.replace(" ", "-");
        b = b.toLowerCase();
        output += "<div class='tab-" + b + "'>" + a + "</div>";
      }
      return this.cjTagMenu.find(".tabs").html(output);
    };

    return View;

  })();

  treeBehavior = {
    autoCompleteStart: function(instance) {
      var params, searchmonger,
        _this = this;

      this.instance = instance;
      cj("#JSTree-data").data({
        "autocomplete": this.instance.getAutocomplete()
      });
      params = {
        jqDataReference: "#JSTree-data",
        hintText: "Type in a partial or complete name of an tag or keyword.",
        theme: "JSTree"
      };
      searchmonger = cj("#JSTree-ac").tagACInput("init", params);
      return cj("#JSTree-ac").on("keydown", function(event) {
        return searchmonger.exec(event, function(terms) {
          return console.log(terms);
        });
      });
    },
    autoCompleteEnd: function(instance) {
      this.instance = instance;
      return cj("#JSTree-ac").off("keydown");
    },
    enableDropdowns: function() {
      cj(".JSTree .treeButton").off("click");
      return cj(".JSTree .treeButton").on("click", function() {
        var tagLabel,
          _this = this;

        tagLabel = cj(this).parent().parent();
        return tagLabel.siblings("dl#tagDropdown_" + (tagLabel.data('tagid'))).slideToggle("200", function() {
          return tagLabel.toggleClass("open");
        });
      });
    }
  };

  /*
  neat
  <script>
  $("div").attr("id", function (arr) {
    return "div-id" + arr;
  })
  .each(function () {
    $("span", this).html("(ID = '<b>" + this.id + "</b>')");
  });
  </script>
  */


}).call(this);
