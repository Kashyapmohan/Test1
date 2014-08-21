<div class="title">3. Deliver To</div>
<div class="wcontent">
    <!-- <input type="button" class="previous" value="previous" id="previous-home" onclick="accChangeTo(2,1);"/>
    <input type="button" class="next" id="next-payment" value="next - payment options >" onclick="deliverParcelFinal();accChangeTo(2,3);" />
    <input type='button' class="nextparcel" value='next-Parcel 2' id='addButton' style="display:none;" onclick="deliverParcelNext();"> -->
    <div class="cls"></div>
    <div id="deliver-item-add-set" class="deliver-item-repeat">
        <div class="deliver-item selected">
            <? $data['from']=$from; ?>
            <? $this->load->view('send/deliver_item.php', $data); ?>
        </div>
    </div>
    <div class="cls"></div>
</div>