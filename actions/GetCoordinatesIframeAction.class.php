<?php
/**
 * gmaps_GetCoordinatesIframeAction
 * @package modules.gmaps.actions
 */
class gmaps_GetCoordinatesIframeAction extends f_action_BaseAction
{
	/**
	 * @param Context $context
	 * @param Request $request
	 */
	public function _execute($context, $request)
	{
		$request->setParameter('address', str_replace('"', '\"', f_util_StringUtils::htmlToText($request->getParameter('address'))));
		return View::SUCCESS;
	}
	
	/**
	 * @return boolean
	 */
	public function isSecure()
	{
		return false;
	}
}