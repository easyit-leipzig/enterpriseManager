<?php
declare(strict_types=1);if(trim((string)file_get_contents(__DIR__."/RELEASE_CANDIDATE"))!=="RC1.8-FC1")exit(1);if(!in_array(trim((string)file_get_contents(__DIR__."/VERSION")),["RC1.8.6-dev-phase68","RC1.8-FC1-HF76"],true))exit(2);echo "RC18_PHASE68_FINAL_CANDIDATE_OK\n";
