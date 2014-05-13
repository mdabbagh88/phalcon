<?php
/**
 * Abstract Class ServiceMeta
 * This class is intentionally left Abstract so that only constants and static functions are available
 * @author "Alan Barber <abarber@cloud9living.com>"
 *
 */
namespace Cloud\Core\Model\App;
Abstract Class ServiceMeta
{
    const SERVICE_DATACACHE          = "data_cache";
    const SERVICE_SESSION            = "session";
    const SERVICE_DATABASE           = "db";
    const SERVICE_DISPATCHER         = "dispatcher";
    const SERVICE_CURRENT_WEBSITE    = "current_website";
    const SERVICE_FRONT_CONTROLLER   = "front_controller";
    const SERVICE_HTTP_RESPONSE      = "response";
    const SERVICE_HTTP_REQUEST       = "request";
    const SERVICE_EVENTS             = "events_manager";
    const SERVICE_URL                = "url";        
    const SERVICE_ROUTER             = "router_default"; //Allow for more possible routers
    const SERVICE_VIEW               = "view";
    const SERVICE_DESIGN             = "design";
    const SERVICE_ASSETS             = "assets";
    const SERVICE_SEO                = "seo";
    const SERVICE_MODELS_MANAGER     = "modelsManager";
    const SERVICE_MODELS_METADATA    = "modelsMetadata";
    const SERVICE_MODELSCACHE        = "modelsCache";
}