var init = "";
var oldwhich = null;
window.map = null;
window.markers = Array();
var forcedSecondlayout = false;
var currentMapocationId = '';
$("#list-send").hide(); // To initially hide the list view
$('#maplocation').change(function() {
    fc = $("#maplocation").val();
    if ($('#sendFrom').val() != fc) {
        $('#sendFrom').val(fc);
        $("#sendFrom").change();
    }
    validatePickUpLocation(fc, function() {
        showlocationmap($("#maplocation option:selected").text());
    });
});
$('#sendFrom').change(function() {
    var fc = $('#sendFrom').val();
    if ($('#maplocation').val() != fc) {
        $('#removemap').trigger('click', 'off');
        $('#maplocation').val(fc);
        $("#clcountry").val(fc);
    }
});
$("#clcountry").change(function(){
    fc = $("#clcountry").val();
    if ($('#sendFrom').val() != fc) {
        $('#sendFrom').val(fc);
        $("#sendFrom").change(fc);
    }
});

window.updateSendfrom = function() {
    var fc = $("#sendFrom :selected").text();
    $("#firstview").hide();
    $("#secondview").hide();
    $('#maplocation').change();
}

function titleChange() {
    var sendMethod = $("#wizard .wcon").eq(1).find('[name="sendmethod"]').val();
    if (sendMethod == 0) {
        $("#wizard .wcon").eq(1).find('.title').html('2. Send from parcel connect location');
    } else {
        $("#wizard .wcon").eq(1).find('.title').html('2. Send from address');
    }
}

window.changeselectionS = function(id, obj) {
    for (var key in window.markers) {
        if (markers[key].latlong == id) {
            google.maps.event.trigger(markers[key], 'click');
        }
    }
}

function showlocationmap(fc) {
    fc = $.trim(fc);
    if (fc != '' && fc != null) {
        //console.log("FC: " +fc);
        $(document).trigger('loader', 'open');
        $.ajax({
            type: "POST",
            url: "<?=base_url('ajax/list_agents')?>",
            data: {
                FranchiseeCode: fc
            }
        }).done(function(res) {
            $(document).trigger('loader', 'close');
            //console.log("Jres: " + res);
            try {
                jres = jQuery.parseJSON(res);
                for (var key in jres) {
                    if (key == 'error') {
                        alert(jres.error);
                        return;
                    }
                }
                $('#nolocation option').remove();
                $.each(jres, function(i, item) {
                    var locationAddress = item[0].AgentName + ", " + item[0].AgentAddress1  + ', ' + item[0].AgentTown + ', ' + item[0].AgentCounty;
					//+ ', ' + item[0].AgentAddress2
                    $locationTemp = item[0].AgentLatitude + '#' + item[0].AgentLongitude
                    $('#nolocation').append($('<option />').text(locationAddress).val($locationTemp));
                });
                mapval = $("#viewloc").val();
                newmap = $.trim(mapval);
                $("#nolocation").val(newmap);
                if (newmap != '') {
                    //$('#nolocation').trigger('change');
                }
                // LIST ITEM
                $('#list-send-inner').html('');
                $.each(jres, function(i, item) {
                    $('#list-send-inner').append("<div class='list-item' id='latlonS" + item[0].AgentLatitude + '#' + item[0].AgentLongitude + "'><a style='font-size:12px;'  onclick=\"changeselectionS('" + item[0].AgentLatitude + '#' + item[0].AgentLongitude + "')\"><p>" + item[0].AgentName + "</p><span>" + item[0].AgentAddress1 + ', ' + item[0].AgentTown + ', ' + item[0].AgentCounty + "</span></a></div>");
					//+ ', ' + item[0].AgentAddress2
                });
                $('#list-send-inner').html();

                $('.list-item').remove();
                $.each(jres, function(i, item) {
                    $('#list-send-inner').append("<div class='list-item' id='latlonS" + item[0].AgentLatitude + '#' + item[0].AgentLongitude + "'><a style='font-size:12px;'  onclick=\"changeselectionS('" + item[0].AgentLatitude + '#' + item[0].AgentLongitude + "')\"><p>" + item[0].AgentName + "</p><span>" + item[0].AgentAddress1 + ', ' + item[0].AgentTown + ', ' + item[0].AgentCounty + "</span></a></div>");
					//+ ', ' + item[0].AgentAddress2
                });
                initialize(jres);
            } catch (err) {
                console.log(err);
                initialize('');
            }
        });
    }
}

$('#removemap').on('click', function(event, data) {
    event.preventDefault();
    var fc = $("#sendFrom :selected").text();
    if (typeof(data) == 'undefined') {
        showlocationmap(fc);
    }
    $("#mapaddress").hide();
    $('#locmapview').show();
    $("#showmapaddress").html('');
    $("#maploc").val('');
    $('#mapSelectedVal').html('None');
    $('#maplocdefault').val('')
    currentMapocationId = '';
    $('#list-send-inner0 .list-item').removeClass('selected');
});

function validatePickUpLocation(value, fn) {
    $(document).trigger('loader', 'open');
    $.ajax({
        type: "POST",
		url: "<?=base_url('ajax/validateSendFrm')?>",
		data: {county:value},
        success: function(response) {
            //console.log(response);
            $(document).trigger('loader', 'close');
            if (response.trim() == 'false') {
                titleChange();
                $('#parcelconnectlocWarning').css({
                    display: ''
                });
                $('#button-switchmapview').css({
                    display: 'none'
                });
                $("#wizard .wcon").eq(1).find('[name="sendmethod"]').val(1)
                titleChange();
                $("#firstview").hide();
                $("#secondview").show();
                $('#maploc').val('');
                initialize('');
            } else {
                fn();
                if (forcedSecondlayout) {
                    $("#wizard .wcon").eq(1).find('[name="sendmethod"]').val(1)
                    titleChange();
                    $("#firstview").hide();
                    $("#secondview").show();
                } else {
                    $("#wizard .wcon").eq(1).find('[name="sendmethod"]').val(0)
                    titleChange();
                    $("#firstview").show();
                    $("#secondview").hide();
                }
                $('#parcelconnectlocWarning').css({
                    display: 'none'
                });
                $('#button-switchmapview').css({
                    display: ''
                });
            }
        },
        error: function(XHR, status, response) {
            $('#loading').hide();
            $("#LoadingImage, #sagePay, #cancel").hide();
            $('#payOnline, #paylater').show();
            alert('Failed Tryagain');
        }
    });
}

//initialize('');

function initialize(init) {
    var CollectFromCountyRf = $("#sendFrom :selected").val();
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
        address: CollectFromCountyRf
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
        window.map = new google.maps.Map(document.getElementById("map_canvas"), map_options);
        var info_window = new google.maps.InfoWindow({
            content: 'loading'
        });

        var t = [];
        var x = [];
        var y = [];
        var h = [];

        $.each(init, function(i, item) {
            t.push(item[0].AgentName);
            x.push(item[0].AgentLatitude);
            y.push(item[0].AgentLongitude);
            h.push(item[0].AgentAddress1 + ',<br> ' + item[0].AgentTown + ', ' + item[0].AgentCounty);
        });
		//+ ', ' + item[0].AgentAddress2
        var mapval = $.trim($("#viewloc").val());
        window.markers = new Array();

        var i = 0;
        for (item in t) {
            var m = new google.maps.Marker({
                map: window.map,
                animation: google.maps.Animation.DROP,
                title: t[i],
                position: new google.maps.LatLng(x[i], y[i]),
                html: h[i],
                icon: "<?= base_url('ingredients/images/image.png') ?>",
                latlong: x[i] + "#" + y[i]
            });
            window.markers.push(m);

            google.maps.event.addListener(m, 'click', function() {
                if (oldwhich !== null) {
                    oldwhich.setIcon("<?= base_url('ingredients/images/image.png') ?>");
                }
                oldwhich = this;
                window.map.panTo(this.position);
                window.map.setZoom(10);
                if (CollectFromCountyRf == 'Dublin, Ireland') {
                    window.map.setZoom(15);
                } else {
                    window.map.setZoom(13);
                }
                $('#locmapview').hide();
                $('#nolocation').val(this.latlong);
                currentMapocationId = this.latlong;
                $("#maploc").val($("#nolocation :selected").text());
                $("#maploc").val($('#nolocation option:selected').text());
                $('#mapSelectedVal').html(this.title);
                $('#maplocdefault').val(this.latlong)
                var dataaddress = "<div class='list-item'><a style='font-size:12px;'><p>" + this.title + "</p><span>" + this.html + "</span></a></div><div class='cls'></div>";
                $("#showmapaddress").html(dataaddress).show();
                $("#mapaddress").show();
                StoreError_flag = 0;
                $("#StoreError").remove();
                this.setIcon("<?= base_url('ingredients/images/image-sel.png') ?>");
                info_window.setContent("<div style='min-width:230px'><b>" + this.title +"</b><br/>"+this.html + "<br/><span style='color:#f00;'>selected</span></div>");
                info_window.open(window.map, this);
            });
            if (m.latlong == $('#maplocdefault').val()) {
                google.maps.event.trigger(m, 'click');
            }
            i++;
        }
        google.maps.event.trigger(map, 'resize');
    })
        .fail(function(jqxhr, textStatus, error) {
            // var err = textStatus + ", " + error;
            // console.log( "Request Failed: " + err );
        });
}

window.listviewS = function() {
    $('#ListView').addClass("viewselected");
    $('#Mapview').removeClass("viewselected");
    $("#list-send").show();
    $("#map_canvas").hide();
    google.maps.event.trigger(map, 'resize');
}
window.mapviewS = function() {
    $('#ListView').removeClass("viewselected");
    $('#Mapview').addClass("viewselected");
    $("#list-send").hide();
    $("#map_canvas").show();
    google.maps.event.trigger(map, 'resize');
    if (currentMapocationId != '') {
        changeselectionS(currentMapocationId);
    }
}

function viewselection() {
    var marker1 = new google.maps.Marker({
        latlong: '53.18807' + "#" + '-6.114990000000034'
    });
    new google.maps.event.trigger(marker1, 'click');
}
$("#button-showsecondlayout").click(function(event) {
    event.preventDefault();
    forcedSecondlayout = true;
    $('#pay_in_store_button').hide();
    $('#wizard .wcon:eq(1) [name="sendmethod"]').val(1);
    titleChange()
    $("#firstview").hide();
    $("#secondview").show();
    accChangeTo(0, 1);
});
$("#button-showsecondlayout2").click(function(event) {
    event.preventDefault();
    forcedSecondlayout = true;
    $('#pay_in_store_button').hide();
    $('#wizard .wcon:eq(1) [name="sendmethod"]').val(1);
    titleChange();
    $("#firstview").hide();
    $("#secondview").show();
});
$("#button-switchmapview").click(function(event) {
    event.preventDefault();
    forcedSecondlayout = false;
    $('#pay_in_store_button').show();
    $('#wizard .wcon:eq(1) [name="sendmethod"]').val(0);
    titleChange();
    $("#firstview").show();
    $("#secondview").hide();
});

validateSendFn = function(state) {
    if (typeof(state) == 'undefined') {
        state = true;
    }
    var obj = $('#wizard .wcon:eq(1)');
    var flag = true;
    var method = obj.find('[name="sendmethod"]').val();
    if (method == 0) {
        var location = obj.find("[name=maploc]");
        var slocation = obj.find("[name=maplocation]");
        var firstName = obj.find("[name=ffname]");
        var surName = obj.find("[name=flname]");
        var email = obj.find("[name=femail]");
        var dphone1 = obj.find("[name=fphone1]");
        var dphone2 = obj.find("[name=fphone3]");
        var checkbox = obj.find('[name="checkbox_ph"]');
        state ? obj.find('.error-msg').remove() : '';
        state ? obj.find('.error').removeClass('error') : '';

        if (slocation.val() == "select country" || slocation.val() == "") {
            state ? $("<span class='error-msg pl-store-location-error'>The location field is required</span>").insertAfter(slocation.addClass('error').next()) : '';
            flag = false;
        }
        if (location.val() == "") {
            state ? $("<span class='error-msg pl_location-error'>The parcel shop field is required</span>").insertAfter(location.addClass('error').next()) : '';
            flag = false;
        }
        if (!name_validate(firstName.val())) {
            state ? $("<span class='error-msg pl-fname-error'>The firstname field is required</span>").insertAfter(firstName.addClass('error')) : '';
            flag = false;
        }
        if (!name_validate(surName.val())) {
            state ? $("<span class='error-msg pl-lastname-error'>The surname field is required</span>").insertAfter(surName.addClass('error')) : '';
            flag = false;
        }
        if (!email_validate(email.val())) {
            state ? $("<span class='error-msg pl-email-error'>The email field is required</span>").insertAfter(email.addClass('error')) : '';
            flag = false;
        }
        if (checkbox.is(":checked")) {
            if (dphone2.val() == "" || !phoneValidateFinal(dphone2.val())) {
                state ? $("<span class='error-msg dphone-error'>The phone number field is required</span>").insertAfter(dphone2.addClass('error').next()) : '';
                flag = false;
            }
        } else {
            if (dphone1.val() == "" || !phoneValidateFinal(dphone2.val(), dphone1.val())) {
                state ? dphone1.addClass('error') : '';
                state ? $("<span class='error-msg dphone-error'>The phone number field is required</span>").insertAfter(dphone2.addClass('error').next()) : '';
                flag = false;
            }
        }
    } else {
        var firstName = obj.find("[name^=clfname]");
        var surName = obj.find("[name^=cllname]");
        var email = obj.find("[name^=clemail]");
        var dphone1 = obj.find("[name^=clphone1]");
        var dphone2 = obj.find("[name^=clphone2]");
        var checkbox = obj.find('[name^="checkbox_clph"]');
        var address1 = obj.find("[name^=claddress1]");
        var town = obj.find("[name^=cltown]");
        var county = obj.find("[name^=clcountry]");
        var postcode = obj.find("[name^=clpostcode]");
        obj.find('span.error-msg').remove();
        obj.find('.error').removeClass('error');
        if (!name_validate(firstName.val())) {
            state ? $("<span class='error-msg dfname-error'>The firstname field is required</span>").insertAfter(firstName.addClass('error')) : '';
            flag = false;
        }
        if (!name_validate(surName.val())) {
            state ? $("<span class='error-msg dlname-error'>The surname field is required</span>").insertAfter(surName.addClass('error')) : '';
            flag = false;
        }
        if (!email_validate(email.val())) {
            state ? $("<span class='error-msg demail-error'>The email field is required</span>").insertAfter(email.addClass('error')) : '';
            flag = false;
        }
        if (checkbox.is(":checked")) {
            if (dphone2.val() == "" || !phoneValidateFinal(dphone2.val())) {
                state ? $("<span class='error-msg dphone-error'>The phone number field is required</span>").insertAfter(dphone2.addClass('error').next()) : '';
                flag = false;
            }
        } else {
            if (dphone1.val() == "" || !phoneValidateFinal(dphone2.val(), dphone1.val())) {
                state ? dphone1.addClass('error') : '';
                state ? $("<span class='error-msg dphone-error'>The phone number field is required</span>").insertAfter(dphone2.addClass('error').next()) : '';
                flag = false;
            }
        }
        if (address1.val() == "") {
            state ? $("<span class='error-msg daddress-error'>The address field is required</span>").insertAfter(address1.addClass('error')) : '';
            flag = false;
        }
        if (town.val() == "") {
            state ? $("<span class='error-msg dtown-error'>The Town is required</span>").insertAfter(town.addClass('error')) : '';
            flag = false;
        }
        if (county.val() == "select a county" || county.val() == "") {
            state ? $("<span class='error-msg dcounty-error'>The County field is required</span>").insertAfter(county.addClass('error')) : '';
            flag = false;
        }
        if (postcode.val() == "") {
            state ? $("<span class='error-msg dpostcode-error'>The postcode field is required</span>").insertAfter(postcode.addClass('error')) : '';
            flag = false;
        }
    }
    return flag;
}


<!-- AJAX LOGIN-->
$('.login-here-link').click(function(){
	$('#login-form-ajx').slideDown();
});
$('.dont-want-to-login').click(function(){
	$('#login-form-ajx').slideUp();
});
$('#submit-ajax-login').click(function(e){
	e.preventDefault();
	var email = $('#email-login').val();
	var password = $('#password-login').val();
	var validate = true; 
	if(email == '')
	{
		$("<span class='error-msg email-error'>The email field is required</span>").insertAfter($('#email-login').addClass('error'));
		validate = false;
	}
	if(password == '')
	{
		$("<span class='error-msg password-error'>The password field is required</span>").insertAfter($('#password-login').addClass('error'));
		validate = false;
	}
	if(validate)
	{
		ajaxLogin(email,password);
	}
	else
	{
		return false;
	}
});
ajaxLogin = function(email,password)
{
	$('#loading').show();
	$.ajax(
	{
		type: "POST",
		url: "<?=base_url('ajax/login')?>",
		data:
		{
			login: email,
			password:password
		}
	})
	.done(function (json)
	{
		jres = jQuery.parseJSON(json);
		if(jres.msg == 'loggedIn')
		{
			$('#cssmenu ul li.login').remove();
			$('#cssmenu ul li.register').remove();
			$('#cssmenu ul').append('<li class="myaccount"><a href="<?=site_url('account/home')?>"><span>My Account</span></a></li>');
			$('#cssmenu ul').append('<li class="logout new"><a href="<?=site_url('access/logout')?>"><span>Logout</span></a></li>');
			$('#ffname').val(jres.firstname);
			$('#flname').val(jres.surname);
			$('#femail').val(jres.email);
			$('#fphone1').val(jres.phone2);
			$('#fphone3').val(jres.phone2);
			$('#login-here').remove();
			$('#login-form-ajx').html("<span class='msg succ-msg'>You have logged in</span>");
			$('#loading').hide();
			
			
		}
		else if(jres.msg == 'loginErr')
		{
			$(".error-msg.email-error").html('');
			$(".error-msg.password-error").html('');
			$(".msg-box").html("<span class='msg err-msg'>Invalid Login</span>");
			$('#loading').hide();
		}
	});

}