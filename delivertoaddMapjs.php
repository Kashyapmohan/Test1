	window.markersId = {};
	window.currentMapocationIdArr = {};

	function initialize_parcel_map(init, id) {
	    var CollectFromCountyRf = $("#pl-location" + id + " :selected").val();		
		if(/^[a-zA-Z0-9- ]*$/.test(CollectFromCountyRf) == false) {
			
			if(CollectFromCountyRf == 'Wicklow (ex Bray)')
			{
				CollectFromCountyRf = 'Wicklow';
			}
			else if(CollectFromCountyRf =='Wicklow (Bray only)')
			{
				CollectFromCountyRf = 'Bray,Wicklow';
			}
		}
	    if (typeof(CollectFromCountyRf) == 'undefined' || CollectFromCountyRf == '') {
	        CollectFromCountyRf = ''
	    } else {
	        CollectFromCountyRf = CollectFromCountyRf + ',Ireland';
	    }
	    $(document).trigger('loader', 'open');
	    $.getJSON("https://maps.googleapis.com/maps/api/geocode/json", {
	        address: CollectFromCountyRf + ',Ireland'
	    }).done(function(json) {
	        $(document).trigger('loader', 'close');
			if(json.results[0].geometry.location.lat)
			{
				var lat = json.results[0].geometry.location.lat;
				var lng = json.results[0].geometry.location.lng;
			}
			else
			{
				var lat = 53.41291;
				var lng = -8.24389;
			}
	        

	        var map_options = {
	            center: new google.maps.LatLng(lat, lng),
	            zoom: 10,
	            mapTypeId: google.maps.MapTypeId.ROADMAP
	        };
	        var google_map = new google.maps.Map(document.getElementById("map_canvas" + id), map_options);
	        var info_window = new google.maps.InfoWindow({
	            content: 'loading'
	        });

	        var t = [];
	        var x = [];
	        var y = [];
	        var h = [];
	        var bounds = new google.maps.LatLngBounds();
	        $('#list-pclocation-send-inner' + id).html('');
	        $('#list-pclocation-send-inner' + id + ' .list-item').remove();
	        $.each(init, function(i, item) {
	            t.push(item[0].AgentName);
	            x.push(item[0].AgentLatitude);
	            y.push(item[0].AgentLongitude);
	            h.push(item[0].AgentAddress1 + ',<br> ' + item[0].AgentTown + ', ' + item[0].AgentCounty);
	        });
			//+ ', ' + item[0].AgentAddress2

	        markersId[id] = new Array();

	        var i = 0;
	        for (item in t) {

	            var pos = new google.maps.LatLng(x[i], y[i]);
	            bounds.extend(pos);

	            var m = new google.maps.Marker({
	                map: google_map,
	                animation: google.maps.Animation.DROP,
	                title: t[i],
	                position: new google.maps.LatLng(x[i], y[i]),
	                html: t[i] + '<br/>' + h[i],
	                icon: '<?= base_url('ingredients/images/image.png') ?>',
	                latlong: x[i] + "#" + y[i]
	            });
	            markersId[id].push(m);
	            google.maps.event.addListener(m, 'click', function() {
	                var locationTmp = this.latlong;
	                currentMapocationIdArr[id] = this.latlong;
	                $('#nolocation-pcl' + id).val(locationTmp);
	                $("#list-pclocation-send-inner'+id+' .list-item").css('color', 'black');
	                var dataaddress = "<div class='list-item'><a style='font-size:12px;'><p>" + this.title + "</p><span>" + this.html + "</span></a></div><div class='cls'></div>";
	                $("#showmapaddress" + id).html(dataaddress).show();
	                $('#mapSelectedVal' + id).html(this.title);
	                $("#pl-store-location" + id).val(this.html);
	                $("#" + id).css('color', 'red');
	                $('#mapaddress' + id).show();
	                $('#pl-location' + id).parent().hide();
	                $('#list' + id + ' option:contains("' + this.title + '")').attr('selected', 'selected');
	                this.setIcon('<?= base_url('ingredients/images/image-sel.png') ?>');
	                info_window.setContent("<div style='width:200px; '><span style='color:#f00;'>selected</span><br/>" + this.html + "</div>");
	                info_window.open(google_map, this);
	            });

	            i++;
	        }
	    });
	}

	function validatePickUpLocation_parcel(value, id) {
	    $(document).trigger('loader', 'open');
	    $.ajax({
	        type: "POST",
	        url: "<?=base_url('ajax/validateSendFrm')?>",
	        data: {county:value},
	        success: function(response) {
	            $(document).trigger('loader', 'close');
	            if (response.trim() == 'false' && $("#collectionstatus").val() == '0') {
	                initialize('');
	            }
	            $('#loading').hide();
	        },
	        error: function(XHR, status, response) {
	            $('#loading').hide();
	            $("#LoadingImage, #sagePay, #cancel").hide();
	            $('#payOnline, #paylater').show();
	            alert('Failed Tryagain');
	        }
	    });
	}

	function showlocationmap_parcel(fc, id) {
	    fc = $.trim(fc);
	    if (fc != '' && fc != null) {
	        $(document).trigger('loader', 'open');
	        $.ajax({
	            type: "POST",
	            url: "<?=base_url('ajax/list_agents')?>",
	            data: {
	                FranchiseeCode: fc
	            }
	        }).done(function(res) {
	            $(document).trigger('loader', 'close');
	            try {
	                jres = jQuery.parseJSON(res);
	                for (var key in jres) break;
	                if (key == 'error') {
	                    alert(jres.error);
	                    return;
	                } else {
	                    $('#nolocation' + id + ' option[value!=" "]').remove();
	                    $.each(jres, function(i, item) {
	                        var locationAddress = item[0].AgentName + ", " + item[0].AgentAddress1 + ', ' + item[0].AgentTown + ', ' + item[0].AgentCounty;
							//+ ', ' + item[0].AgentAddress2
	                        $locationTemp = item[0].AgentLatitude + '#' + item[0].AgentLongitude
	                        $('#nolocation' + id).append($('<option />').text(locationAddress).val($locationTemp));
	                        $('#maploc' + id).val(locationAddress);

	                    });

	                    mapval = $("#viewloc").val();
	                    newmap = $.trim(mapval);
	                    $("#nolocation" + id).val(newmap);
	                    if (newmap != '') {
	                        $('#nolocation' + id).trigger('change');
	                    }

	                    $('#list-send-inner' + id).html('');
	                    $.each(jres, function(i, item) {
	                        $('#list-send-inner' + id).append("<div class='list-item' id='latlon" + item[0].AgentLatitude + '#' + item[0].AgentLongitude + "'><a style='font-size:12px;'  onclick=\"changeselection('" + item[0].AgentLatitude + '#' + item[0].AgentLongitude + "', " + id + ")\"><p>" + item[0].AgentName + "</p><span>" + item[0].AgentAddress1 + ', ' + item[0].AgentTown + ', ' + item[0].AgentCounty + "</span></a></div>");
							//+ ', ' + item[0].AgentAddress2
	                    });
	                    $('#list-send-inner' + id).html();


	                    initialize_parcel_map(jres, id);
	                }
	            } catch (err) {
	                console.log(err);
	                initialize_parcel_map('');
	            }
	        });
	    }
	}
	window.changeselection = function(id, objid) {
	    for (var key in window.markersId[objid]) {
	        if (markersId[objid][key].latlong == id) {
	            google.maps.event.trigger(markersId[objid][key], 'click');
	        }
	    }
	}

	function getcounty_parcel(countrycode, counter) {
	    //$(document).trigger('loader','open');
	    $.ajax({
	        type: "POST",
	        url: "<?=base_url('welcome/getcountrycode')?>",
	        data: {
	            countrycode: countrycode,
	            counter: counter
	        },
	        success: function(response) {
	            //$(document).trigger('loader','close');
	            jres = jQuery.parseJSON(response);
	            for (var key in jres) break;
	            $('#dphone1' + jres.counter).children().remove();
	            $('#pl_phone1' + jres.counter).children().remove();

	            var phonedata = jres.phonecode;

	            for (var i = 0; i < phonedata.length; i++) {
	                $('#dphone1' + jres.counter).append($('<option />').text(phonedata[i].code).val(phonedata[i].code));
	                $('#pl_phone1' + jres.counter).append($('<option />').text(phonedata[i].code).val(phonedata[i].code));
	            }

	            var ddl1 = jres.result;

	            for (var i = 0; i < ddl1.length; i++) {
	                $('#dcountry' + jres.counter).append($('<option />').text(ddl1[i].name).val(ddl1[i].name));
	            }
	        },
	        failure: function(errMsg) {
	            $('#errMessage').text(errMsg);
	        }
	    });
	};

	window.mapview = function(id) {
	    $('#list-send' + id).hide();
	    $('#map_canvas' + id).show();
	    $('#Mapview' + id).addClass("viewselected");
	    $('#ListView' + id).removeClass("viewselected");
	    if (currentMapocationIdArr[id] != '') {
	        changeselection(currentMapocationIdArr[id], id);
	    }
	}

	window.listview = function(id) {
	    $('#list-send' + id).show();
	    $('#map_canvas' + id).hide();
	    $('#ListView' + id).addClass("viewselected");
	    $('#Mapview' + id).removeClass("viewselected");
	}
	window.init = function(id) {
	    $('#list-pclocation-send-inner').html('Select a County');
	    initialize_parcel_map(jres);
	    $("#pl-location" + id).trigger('change');
	    $('#list-send').hide();
	    mapview(id);
	    $("#pl-location" + id).change(function() {
	        var fc = $(this).val();
	        //validatePickUpLocation_parcel(fc);
	        showlocationmap_parcel(fc, id);
	        $('#list-pclocation-send-inner').html('Select a County');
	    });
	    $('#removemap' + id).on('click', function(event) {
	        event.preventDefault();
	        var fc = $("#pl-location" + id + " :selected").val();
	        $('#pl-location').show();
	        //validatePickUpLocation_parcel(fc, id);
	        showlocationmap_parcel(fc, id);
	        $("#mapaddress" + id).hide();
	        $('#locmapview' + id).show();
	        $("#showmapaddress" + id).html('').hide();
	        $('#mapSelectedVal' + id).html('None');
	        $('#pl-location' + id).parent().show();
	        $("#pl-store-location" + id).val('');
	        currentMapocationIdArr[id] = '';
	    });

	}