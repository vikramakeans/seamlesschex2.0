(function () {
  
  'use strict';
  
  angular
    .module('authApp.timepicker', [
      'authApp.utils'
    ])
    .directive('timepicker', [
      '$window', '$document', 'utils',
      'elemUtils', 'dateUtils', timepicker
    ])
    .directive('timeInput', ['$filter', 'dateUtils', timeInput])
    .directive('renderOnBlur', renderOnBlur)
    .directive('renderOnEnter', renderOnEnter);
  
  function timepicker ($window, $document, utils, elemUtils, dateUtils) {

      return {
        restrict: 'EA',
        scope: {
          'theTime': '=ngModel',
        },
        controller: ['$scope', 'utils', 'dateUtils', TimepickerController],
        replace: true,
        // Template at the bottom of the HTML section 
        templateUrl: '../admin/views/template/timepicker.html',
        link: linker,
      };

      function TimepickerController ($scope, utils, dateUtils) {
        
        var self = this;
        
        $scope.choiceTimes = [];
        $scope.generateChoiceTimes = _generateChoiceTimes;
        $scope.selectPrevTime = _selectPrevTime;
        $scope.selectNextTime = _selectNextTime;
        $scope.isSelectedTime = _isSelectedTime;
        $scope.showingChoiceList = false;
        $scope.showChoiceList = _showChoiceList;
        $scope.hideChoiceList = _hideChoiceList;
        $scope.toggleChoiceList = _toggleChoiceList;
        $scope.setTime = _setTime;
        
        // Event listeners

        $scope.$watch('theTime', function (newVal, oldVal) {
          if (newVal) { $scope.selectedTime = newVal; }
        });
        
        // Allow the linker to pass a reference to the
        // directive element.
        this.init = function (element, inputField, dropdown) {
          this.element = element;
          this.inputField = inputField;
          this.dropdown = dropdown;
        };
        
        // Functions

        function _generateChoiceTimes (startingTime) {
          for(var i=0; i<48; i++) {
            // Create new time and normalize to Jan 1, 1970
            var newTime = dateUtils.dateAdd(
              startingTime, 'minute', 30*i
            );
            newTime.setYear(70);
            newTime.setMonth(1);
            newTime.setDate(1);
            // Add to list of choices
            $scope.choiceTimes.push(newTime);
          }
        }

        function _selectPrevTime () {

          // TODO: choiceTimes could be managed better with
          //       something like a circular linked list
          //       with next and prev methods on each node.

          // Don't change the selection if dropdown is not showing
          if (!$scope.showingChoiceList) { return; }

          // Get the next index value, based on the current selection.
          // If this is the first item, wrap around to the last item
          var selectedIndex = _getSelectedTimeIndex();

          var nextIndex = selectedIndex === 0 ? $scope.choiceTimes.length - 1
                                              : selectedIndex - 1;
          
          // Update the selected time
          $scope.selectedTime = $scope.choiceTimes[nextIndex];
          $scope.$apply();
          
        }
        
        function _selectNextTime () {

          // TODO: choiceTimes could be managed better with
          //       something like a circular linked list
          //       with next and prev methods on each node.

          // Don't change the selection if dropdown is not showing
          if (!$scope.showingChoiceList) { return; }

          // Get the next index value, based on the current selection.
          // If this is the first item, wrap around to the last item
          var selectedIndex = _getSelectedTimeIndex();
          var nextIndex = selectedIndex === $scope.choiceTimes.length - 1
                                              ? 0 : selectedIndex + 1;

          // Update the selected time
          $scope.selectedTime = $scope.choiceTimes[nextIndex];
          $scope.$apply();

        }
        
        /** 
         * Date objects cannot be directly compared. Instead,
         * coerce to a `Number` before making the comparison.
         **/
        function _isSelectedTime (time) {
          return Number(time) === Number($scope.selectedTime);
        }
        
        /**
         * Like the Array.indexOf() method: find the index of
         * the current time selection in the array or return `-1`.
         **/
        function _getSelectedTimeIndex () {
          for (var i = 0; i < $scope.choiceTimes.length; i++) {
            if (_isSelectedTime($scope.choiceTimes[i])) {
              return i;
            }
          }
          return -1;
        }
        
        function _showChoiceList (event) {
          if ($scope.showingChoiceList === true) { return; }
          $scope.showingChoiceList = true;
          $scope.$apply();
          $document.on('click', _hideChoiceList);
        }

        function _hideChoiceList (event) {
          if ($scope.showingChoiceList === false) { return; }
          if (event && (self.inputField[0].contains(event.target)
                        || self.dropdown[0].contains(event.target))) {
            return;
          }
          $scope.showingChoiceList = false;
          $scope.$apply();
          $document.off('click', _hideChoiceList);
        }

        function _toggleChoiceList (event) {
          if ($scope.showingChoiceList) { _hideChoiceList(event); }
          else { _showChoiceList(event); }
          $scope.$apply();
        }

        function _setTime(time) {
          $scope.theTime = time;
        }

      }

      function linker (scope, element, attrs, tpCtrl) {
        
        // initialize field value
        var inputField = element.find('input'),
            dropdown = angular.element(
                element[0].querySelector('.timepicker-dropdown')
            );

        // Pass element references to controller
        tpCtrl.init(element, inputField, dropdown);

        // Initialize scope variables
        scope.labelText = attrs.label || '';
        scope.theTime = attrs.defaultTime 
                          ? dateUtils.parseTimeStringToDate(attrs.defaultTime)
                          : new Date(1970, 1, 1, 0, 0, 0, 0);
        scope.selectedTime = scope.theTime;

        scope.generateChoiceTimes(scope.selectedTime);

        // Event Listeners
        inputField.on('focus + click', function (event) {
          inputField[0].select();
          scope.showChoiceList();
          _updateDropdownPosition();
          scope.$apply();
          _updateDropdownScroll();
        });

        inputField.on('keydown', function (event) {
          switch(event.keyCode) {
            case 38: // Up arrow
              scope.selectPrevTime();
              _updateDropdownScroll();
              break;
            case 40: // Down arrow
              scope.selectNextTime();
              _updateDropdownScroll();
              break; 
            case 13: // Enter key
              scope.setTime(scope.selectedTime);
              scope.hideChoiceList();
              break;
            default: // Any other key
              // Any other should close the dropdown
              scope.hideChoiceList();
              _updateDropdownScroll();
              break;
          }
        });
        
        //
        dropdown.on('click', function (event) {
          inputField[0].focus();
          scope.$apply();
          scope.hideChoiceList();
        })

        // Hide the dropdown menu while resizing. Debounce for performance.
        $window.addEventListener('resize', utils.debounce(function () {
          dropdown.css({visibility: 'hidden'});
        }, 50, true));
        $window.addEventListener('resize', utils.debounce(function () {
          _updateDropdownPosition();
          dropdown.css({visibility: 'visible'});
        }, 50));
        
        // Functions
        
        function _updateDropdownScroll () {
          var selectedTimeDiv = _getSelectedTimeDiv();
          if (selectedTimeDiv) {
            dropdown[0].scrollTop = selectedTimeDiv.offsetTop;
          }
        }
        
        /**
         * Get div corresponding to selected time.
         * ids are in the format of 'd' + timestamp
         */
        function _getSelectedTimeDiv () {
          return dropdown[0].querySelector(
            '#d' + String(Number(scope.selectedTime))
          );
        }

        function _updateDropdownPosition () {
          // Reposition the dropdown.
          // This needs to be called after the dropdown has been revealed, in
          // order for the width of the dropdown to be read properly.
          var dropdownPosition = elemUtils.below(
            inputField[0], dropdown[0]
          );
          dropdown.css('top', dropdownPosition.top + 'px');
          dropdown.css('left', dropdownPosition.left + 'px');
        }

      }
  }

  function timeInput ($filter, dateUtils) {

      return {
          restrict: 'A',
          require: 'ngModel',
          link: linker,
      };

      function linker (scope, element, attrs, ngModelCtrl) {

        var dateFormat = 'h:mm a';

        ngModelCtrl.$formatters = [function (modelValue) {
          return $filter('date')(modelValue, dateFormat);
        }];

        ngModelCtrl.$parsers.unshift(function (viewValue) {
          return dateUtils.parseTimeStringToDate(viewValue);
        });

      }

  }

  /**
   * Re-renders the ng-model attached to an input when the input
   * loses focus.
   **/
  function renderOnBlur () {

      return {
          restrict: 'A',
          require: 'ngModel',
          link: linker,
      };

      function linker (scope, element, attrs, ngModelCtrl) {
          element.on('blur', function () {
              if (!ngModelCtrl.$modelValue) { return; }
              var viewValue = ngModelCtrl.$modelValue;
              for (var i = 0; i < ngModelCtrl.$formatters.length; i++) {
                  viewValue = ngModelCtrl.$formatters[i](viewValue);
              }
              ngModelCtrl.$viewValue = viewValue;
              ngModelCtrl.$render();
          });
      }

  }

  /**
   * Re-renders the ng-model attached to an input when the
   * user presses the `Enter` key while inside the input.
   **/
  function renderOnEnter () {

      return {
          restrict: 'A',
          require: 'ngModel',
          link: linker,
      };

      function linker (scope, element, attrs, ngModelCtrl) {
          element.on('keydown', function (event) {
            if(event.keyCode === 13) {
              event.preventDefault();
              event.stopPropagation();
              if (!ngModelCtrl.$modelValue) { return; }
              var viewValue = ngModelCtrl.$modelValue;
              for (var i = 0; i < ngModelCtrl.$formatters.length; i++) {
                  viewValue = ngModelCtrl.$formatters[i](viewValue);
              }
              ngModelCtrl.$viewValue = viewValue;
              ngModelCtrl.$render();              
            }
          });
      }

  }
  
})();
  
/////// Utility Services
(function () {

  angular
    .module('authApp.utils', [])
    .factory('utils', utils)
    .factory('elemUtils', elemUtils)
    .factory('dateUtils', dateUtils)
    .filter('dateToTimestamp', dateToTimestamp);
  
  function dateToTimestamp () {
	  var date;
    return function (date) {
      return date.valueOf();
    };
  }
  
  function utils () {
    
    var exports = {
      debounce: _debounce,
      objIndexFromId: _objIndexFromId,
    };
    
    return exports;

    function _objIndexFromId (source, idVal, idKey) {
      /* Retrieve index of object with key and value that match those provided */
      idKey = typeof idKey !== 'undefined' ? idKey : 'id';
      return source.map(function(x) {return x[idKey]; }).indexOf(idVal);
    }
    
    // Debounce function from David Walsh's blog
    // Returns a function, that, as long as it continues to be invoked, will not
    // be triggered. The function will be called after it stops being called for
    // N milliseconds. If `immediate` is passed, trigger the function on the
    // leading edge, instead of the trailing.
    function _debounce(func, wait, immediate) {
      var timeout;
      return function() {
        var context = this, args = arguments;
        var later = function() {
          timeout = null;
          if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
      };
    }
    
  }
  
  /**
   * Library of useful DOM Element operations
   **/
  function elemUtils () {

    var exports = {
      // dimensions
      outerWidth: _outerWidth,
      outerHeight: _outerHeight,
      animationDuration: _animationDuration,
      // positioning
      above: _above,
      below: _below,
      centerOffset: _centerOffset,
    };

    return exports;

    // Element dimension functions

    function _outerWidth (element) {
      /** Get the width of an element including margins */
      var elemCSS = window.getComputedStyle(element);
      return element.offsetWidth
        + (parseFloat(elemCSS.marginLeft, 10) || 0)
        + (parseFloat(elemCSS.marginRight, 10) || 0);
    }

    function _outerHeight (element) {
      /** Get the height of an element including margins */
      var elemCSS = window.getComputedStyle(element);
      return element.offsetHeight 
        + (parseFloat(elemCSS.marginTop, 10) || 0)
        + (parseFloat(elemCSS.marginBottom, 10) || 0);      
    }

    function _animationDuration (element) {
      /* Get the computed animation duration on the element */
      var elementCSS = window.getComputedStyle(element);
      return parseFloat(elementCSS['animation-duration'], 10);
    }

    // Element positioning functions

    function _above (parent, child) {
      /* Get child position that centers it above parent */
      throw new Error('The `Position.above()` method is not yet implemented.');
    }

    function _below (parent, child) {
      /* Get child position that centers it below parent */

      var childLeft = _centerOffset(parent).left - (_outerWidth(child) / 2);
      var childTop = parent.offsetTop + parent.offsetHeight;

      // Align the child element to the left side of the screen
      // in edge cases where it would overflow the left side
      // or where the screen is skinnier than the child element.
      if (childLeft < 0 || window.innerWidth <= _outerWidth(child)) {
        childLeft = 0;
      } else if (childLeft + _outerWidth(child) > window.innerWidth) {
        // Ensure the popover doesn't overflow the right side of the screen.
        childLeft = window.innerWidth - _outerWidth(child);
      }

      return {
        top: childTop,
        left: childLeft,
      };

    }

    function _centerOffset (element) {
      /* Get the left offset (in pixels) of the center of an element */
      return {
        left: element.offsetLeft + element.offsetWidth / 2,
        top: element.offsetTop + element.offsetHeight / 2,
      };
    }

  }
  
  /**
  * This service provides some shortcut functions for
  * working with dates, especially dates in ISO format,
  * which is the date format Angular prefers when working
  * with date inputs.
  */
  function dateUtils () {

    return {
      dateAdd: _dateAdd,
      parseTimeStringToDate: _parseTimeStringToDate,
    };
	
    function _dateAdd(date, interval, units) {
      var ret = new Date(date); // don't change original date
      switch(interval.toLowerCase()) {
        case 'year'   :  ret.setFullYear(ret.getFullYear() + units);  break;
        case 'quarter':  ret.setMonth(ret.getMonth() + 3*units);  break;
        case 'month'  :  ret.setMonth(ret.getMonth() + units);  break;
        case 'week'   :  ret.setDate(ret.getDate() + 7*units);  break;
        case 'day'    :  ret.setDate(ret.getDate() + units);  break;
        case 'hour'   :  ret.setTime(ret.getTime() + units*3600000);  break;
        case 'minute' :  ret.setTime(ret.getTime() + units*60000);  break;
        case 'second' :  ret.setTime(ret.getTime() + units*1000);  break;
        default       :  ret = undefined;  break;
      }
      return ret;
    }

    /**
     * Parses partial and complete time strings to a date object.
     * Makes time input a lot more user-friendly.
     *
     * For example, all of the following string inputs should be
     * parsed to a date object of today's date and time of 1pm.
     *
     *    var times = ['1:00 pm','1:00 p.m.','1:00 p','1:00pm',
     *                 '1:00p.m.','1:00p','1 pm','1 p.m.','1 p',
     *                 '1pm','1p.m.', '1p','13:00','13'];
     *
     * NOTE: This version is optimized for the en-US locale in
     *       either 12-hour or 24-hour format. It may not be
     *       suitable for all locales. Instaed of re-writing this
     *       function, it would make more sense to extend our
     *       capabilities with locale-specific functions.
     **/
    function _parseTimeStringToDate (timeString) {

      if (timeString == '') return null;

      var time = timeString.match(/(\d+)(:(\d\d))?\s*(p?)/i); 
      if (time == null) return null;

      var hours = parseInt(time[1],10);

      if (hours == 12 && !time[4]) { hours = 0; }
      else { hours += (hours < 12 && time[4])? 12 : 0; }

      var d = new Date(1970, 1, 1, 0, 0, 0);
      d.setHours(hours);
      d.setMinutes(parseInt(time[3],10) || 0);
      d.setSeconds(0, 0);
      return d;

    }
    
  }
    
})();


