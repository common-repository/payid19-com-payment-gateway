<?php
wp_enqueue_style( 'payid19-public-css',plugin_dir_url( __FILE__ ) . 'css/payid19-public.css', '1.0.0', 'all' );
wp_enqueue_style( 'bootstrap','https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', '5.3.2', 'all' );
wp_enqueue_style( 'toastr', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css', '2.1.3', 'all' );
wp_enqueue_style( 'bootstrap-icons','https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css', '1.11.1', 'all' );

wp_enqueue_script( 'payid19-public-js', plugin_dir_url( __FILE__ ) . 'js/payid19-public.js', array( 'jquery' ), '1.0.0', false );
wp_enqueue_script( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js','5.3.2',  false );
wp_enqueue_script( 'toastr', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js','2.1.3',  false );
wp_enqueue_script( 'qrcode', 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js','1.0.0',  false );
wp_enqueue_script( 'clipboard', 'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.4.0/clipboard.min.js','1.4.0',  false );
wp_enqueue_script( 'sweetalert', 'https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js','2.1.2',  false );
wp_enqueue_script( 'ddslick', 'https://cdn.rawgit.com/prashantchaudhary/ddslick/master/jquery.ddslick.min.js','2',  false );
?>

<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal" onclick="jQuery('#myModal').modal('show')">Open Payment Box</button>
<div class="modal fade mt-4" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel"><?php esc_html_e('DO PAYMENT WITH CRYPTO','payid19')?></h5>
			</div>
			<div class="modal-body">
				<input type="text" style="display:none;" name="ajaxurl" id="ajaxurl" value="<?php echo esc_url(admin_url('admin-ajax.php'));?>">
				<input type="text" style="display:none;" name="order_id" id="order_id" value="<?php echo esc_html($order_id); ?>">
				<div id="coin-select-section">
					<small id="passwordHelpBlock" class="form-text text-muted">
						<?php esc_html_e('Which coin would you like to pay with?','payid19')?>
					</small>
					<select  id="select_coin_select_box" class="form-control">
						<option data-imagesrc="" data-description="" value=""><?php esc_html_e('Firstly, Select a coin?','payid19')?></option>
						<?php foreach($coins as $coin){ $invoice_id=$coin->invoice_id; ?>
							<option value="<?php echo esc_html($coin->name).'-'.esc_html($coin->network);?>"><?php echo esc_html($coin->name); if($coin->name!=$coin->network){echo ' -'.esc_html($coin->network);}?></option>
						<?php } ?>
					</select>
				</div>
				<hr class="mt-2 mb-1"/>
				<div id="loading1" class="text-center mt2" style="display:none;">
					<div class="spinner-border" role="status" style="width: 3rem; height: 3rem;">
						<span class="sr-only"></span>
					</div>
				</div>
				<div class="row " id="last_section" style="display:none;" >
					<div class="d-none d-sm-block col-xs-4 col-sm-4 col-md-4 col-lg-4	col-xl-4 pe-0" id="qrcode"></div>
					<div class="col-12 col-xs-8 col-sm-8 col-md-8 col-lg-8	col-xl-8  ps-0" >
						<table class="table table-borderless mb-0" style="word-wrap: break-word; font-size: 12px;">
							<tbody>
								<tr>
									<td class="pe-0 ps-0"><div id="coin_icon"></div><strong><?php esc_html_e('Address','payid19')?></strong></td>
									<td id="address" style="word-break: break-all;"></td>
									<td class="px-2"><i class="bi bi-clipboard copy" style="cursor: pointer; font-size: 1.2rem;" data-clipboard-target="#address" id="clipcoard_address"></i></td>
								</tr>
								<tr>
									<td  class="pe-0 ps-0"><strong><?php esc_html_e('Amount','payid19')?></strong></td>
									<td id="amount" style="word-break: break-all;"></td>
									<td class="px-2"><i class="bi bi-clipboard copy" style="cursor:pointer; font-size: 1.2rem;" data-clipboard-target="#amount" id="clipcoard_amount"></i></td>
								</tr>
							</tbody>
						</table>
						<div><small class="text-info"><small><?php esc_html_e('send the indicated amount to the address below','payid19')?></small></small></div>
					</div>
					<div class="row">
						<div class="col-4"></div>
						<div class="col-8">
							<div class="row">
								<div class="col-2">
									<div class="spinner-border spinner-border-sm" role="status">
										<span class="visually-hidden"><?php esc_html_e('Loading','payid19')?>...</span>
									</div>
								</div>
								<div class="col-10">
									<small><?php esc_html_e('Waiting Payment','payid19')?></small>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	(function( $ ) {
		jQuery(document).ready(function(){
			$('#myModal').modal('show');
		});

		jQuery(document).ready(function($) {
			$('#select_coin_select_box').on('change', function() {
				get_address();
			})
		})

		function get_address(){
			$("#loading1").show();
			$("#last_section").hide();

			let value = $('#select_coin_select_box').find(":selected").val()
			var coinNetwork = value.split("-");
			console.log(value)
			var data = {
				'email': '<?php echo esc_js($order->get_billing_email());?>',
				'coin':coinNetwork[0],
				'invoice_id': <?php echo esc_js($invoice_id);?>,
				'network' :coinNetwork[1]
			};

			jQuery.post('https://payid19.com/api/v1/get_address', data, function(data) {
				if(data.status=='error'){
					do_swal(data,data.status,'Done');
				}else{
					qrcode('qrcode',data.address);
					$('#address').html(data.address);
					$('#amount').html(data.amount);
					$("#last_section").show();
					$("#loading1").hide();
					$(".bi-clipboard-check-fill").removeClass('bi-clipboard-check-fill').addClass('bi-clipboard');
				}
			});
		}

		function doControl(){
			var interval = Math.floor(Math.random() * (10000 - 5000)+5000);
			var data = {
				'action': 'do_control',
				'order_id':$('#order_id').val(),
				'address':$('#address').html(),
				'amount':$('#amount').html()
			};

			if(data.address!=''){
				jQuery.post('<?php echo esc_url(admin_url('admin-ajax.php'));?>', data, function(response) {
					var result=JSON.parse(response);
					if(result.status=='success'){
						window.location.replace(result.redirect_url);
					}else if(result.status=='failed' || result.status=='cancelled' || result.status=='refunded'){
						window.location.replace(result.redirect_url);
					}else if(result.status=='pending'){
						setTimeout(doControl,interval);
					}
				});
			}else{
				setTimeout(doControl,interval);
			}
		}

		jQuery(document).ready(function(){
			setTimeout(doControl,5000);
		});

		function qrcode(qrcode,text){
			jQuery("#"+qrcode).html('');
			var qrcode = new QRCode(document.getElementById(qrcode), {
				text: text,
				width: 128,
				height: 128,
				colorDark : "#000000",
				colorLight : "#ffffff",
				correctLevel : QRCode.CorrectLevel.H
			});
		}

		function do_swal(data,icon,button,refresh=null){
			if(refresh!=null){

				swal({
					title: data.title,
					text: data.message,
					icon: icon,
					button: button,
				}).then(function(){ 
					if(refresh=='refresh'){
						location.reload();
					}else{
						window.location = refresh; 
					}
				});    
			}else{
				swal({
					title: data.title,
					text: data.message,
					icon: icon,
					button: button,
				});         
			}
		}

		jQuery(document).ready(function(){
			var clipboard = new ClipboardJS('.copy');
			clipboard.on('success', function(e) {
				const query = e.trigger.getAttribute('id');
				$(".bi-clipboard-check-fill").removeClass('bi-clipboard-check-fill').addClass('bi-clipboard');
				$("#"+query).addClass('bi-clipboard-check-fill').removeClass('bi-clipboard');
				e.clearSelection();
			});
			clipboard.on('error', function(e) {
				toastr.error('Error, Dont Copied!')      
			});
		});

	})( jQuery );
</script>
