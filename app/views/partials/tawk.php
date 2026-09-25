<?php
/** @var array $config */
$tawk = is_array($config['tawk'] ?? null) ? $config['tawk'] : [];
$tawkEnabled = !empty($tawk['enabled']);
$tawkSrc = trim((string) ($tawk['embed_src'] ?? ''));
if ($tawkSrc === '' && !empty($tawk['property_id']) && !empty($tawk['widget_id'])) {
    $tawkSrc = 'https://embed.tawk.to/' . rawurlencode((string) $tawk['property_id']) . '/' . rawurlencode((string) $tawk['widget_id']);
}
if (!$tawkEnabled || $tawkSrc === '') {
    return;
}
?>
<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src=<?= json_encode($tawkSrc, JSON_UNESCAPED_SLASHES) ?>;
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
