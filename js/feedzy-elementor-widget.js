window.addEventListener( 'elementor/init', function() {
	var fzLayoutTemplate = elementor.modules.controls.BaseData.extend({
		onReady() {
			var self = this;
			this.template_control = this.$el.find('input[name="fz-layout-template"]');
			this.template_control.change( function() {
				self.saveValue( jQuery( this ).val() );
			} );
			console.log( this.model.get( 'template_options' ) );
		},
    saveValue( value ) {
			this.setValue(value);
		},
		onBeforeDestroy() {
			this.saveValue('default');
		}
	});
	elementor.addControlView( 'fz-layout-template', fzLayoutTemplate );
} );
