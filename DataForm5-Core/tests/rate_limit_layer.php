<?php
declare(strict_types=1);
use DataForm5\RateLimit\Contracts\RateLimiterInterface;use DataForm5\RateLimit\Core\RateLimiter;use DataForm5\RateLimit\Stores\InMemoryRateLimitStore;use DataForm5\RateLimit\Exceptions\RateLimitException;
require dirname(__DIR__).'/bootstrap/autoload.php';
$limiter=new RateLimiter(new InMemoryRateLimitStore(),'test');
$r1=$limiter->attempt('user:7',3,60);$r2=$limiter->attempt('user:7',3,60);$r3=$limiter->attempt('user:7',3,60);$r4=$limiter->attempt('user:7',3,60);
assert($r1->allowed&&$r1->remaining===2);assert($r3->allowed&&$r3->remaining===0);assert(!$r4->allowed&&$r4->retryAfter>0);assert(isset($r4->headers()['Retry-After']));
$q=$limiter->quota('project:42',10,3600,4);assert($q->allowed&&$q->used===4&&$q->remaining===6);
$limiter->clear('user:7');assert($limiter->inspect('user:7',3,60)->remaining===3);
$invalid=false;try{$limiter->attempt('',1,60);}catch(RateLimitException){$invalid=true;}assert($invalid);
$kernel=require dirname(__DIR__).'/bootstrap/app.php';assert($kernel->container()->get(RateLimiterInterface::class) instanceof RateLimiter);
echo "PASS: Rate Limit, Quota and Protection Layer\n";
