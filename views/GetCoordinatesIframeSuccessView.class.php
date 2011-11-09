<?php
/**
 * @package modules.gmaps.views
 */
class gmaps_GetCoordinatesIframeSuccessView extends f_view_BaseView
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$this->setTemplateName('Gmaps-Action-GetCoordinatesIframe-Success');
		$this->setAttributes($request->getParameters());
	}
}