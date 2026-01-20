<?php

if (!function_exists("placeholder")) {
    function placeholder($path, $image = "placeholder.webp")
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $path = $path;
        } else if (app()->environment("production") && !empty($path)) {
            $path = asset("storage/" . $path);
        } else if ($path == "" or $path == null or empty($path)) {
            $path = asset("images/" . $image);
        } else if (!empty($path)) {
            $path = asset("storage/" . $path);
        }
        return $path;
    }
}
if (!function_exists("avatar")) {
    function avatar($path)
    {
        return placeholder($path, "avatar.webp");
    }
}