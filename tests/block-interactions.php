<?php

declare(strict_types=1);

use NhanAZ\DebugStick\Main;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\permission\Permission;
use pocketmine\player\Player;
use pocketmine\world\World;

$source = $argv[1] ?? null;
if ($source === null || !is_file($source . '/vendor/autoload.php')) {
	fwrite(STDERR, "Usage: php tests/block-interactions.php /path/to/Axolotl-PM-source [plugin-main]\n");
	exit(2);
}
require $source . '/vendor/autoload.php';
require $argv[2] ?? dirname(__DIR__) . '/src/NhanAZ/DebugStick/Main.php';

final class DebugStickTestWorld extends World {
	public ?Block $writtenBlock = null;
	public int $writes = 0;

	public function isLoaded(): bool {
		return true;
	}

	public function setBlock(Vector3 $pos, Block $block, bool $update = true): void {
		$this->writtenBlock = clone $block;
		++$this->writes;
	}
}

final class DebugStickTestPlayer extends Player {
	public bool $allowed = true;
	public bool $sneaking = false;
	public string $tip = '';
	public string $message = '';

	public function getName(): string {
		return 'Tester';
	}

	public function hasPermission(Permission|string $name): bool {
		return $this->allowed;
	}

	public function isSneaking(): bool {
		return $this->sneaking;
	}

	public function sendTip(string $message): void {
		$this->tip = $message;
	}

	public function sendMessage(\pocketmine\lang\Translatable|string $message): void {
		$this->message = is_string($message) ? $message : '';
	}

	public function __destruct() {}
}

$plugin = (new ReflectionClass(Main::class))->newInstanceWithoutConstructor();
$world = (new ReflectionClass(DebugStickTestWorld::class))->newInstanceWithoutConstructor();
$player = (new ReflectionClass(DebugStickTestPlayer::class))->newInstanceWithoutConstructor();
$stick = VanillaItems::STICK();
$stick->getNamedTag()->setByte('debugstick', 1);

$checkWrite = static function (DebugStickTestWorld $world, Block $block, int $count): void {
	if ($world->writtenBlock === null || $world->writtenBlock->getStateId() !== $block->getStateId() || $world->writes !== $count) {
		throw new RuntimeException('Block state was not written to the world');
	}
};

$interact = static function (Block $block, int $action) use ($plugin, $player, $stick): PlayerInteractEvent {
	$event = new PlayerInteractEvent($player, $stick, $block, null, Facing::UP, $action);
	$plugin->onPlayerInteract($event);
	return $event;
};

$anvil = VanillaBlocks::ANVIL();
$anvil->position($world, 1, 2, 3);
$anvil->setFacing(Facing::NORTH);
$event = $interact($anvil, PlayerInteractEvent::RIGHT_CLICK_BLOCK);
if (!$event->isCancelled() || $anvil->getFacing() !== Facing::SOUTH) {
	throw new RuntimeException('Anvil facing did not change');
}
$checkWrite($world, $anvil, 1);

$bamboo = VanillaBlocks::BAMBOO();
$bamboo->position($world, 2, 2, 3);
$player->sneaking = true;
$interact($bamboo, PlayerInteractEvent::RIGHT_CLICK_BLOCK);
$player->sneaking = false;
$interact($bamboo, PlayerInteractEvent::LEFT_CLICK_BLOCK);
if (!$bamboo->isReady()) {
	throw new RuntimeException('Bamboo ready state did not toggle on left click');
}
$checkWrite($world, $bamboo, 2);

$player->sneaking = true;
$interact($bamboo, PlayerInteractEvent::RIGHT_CLICK_BLOCK);
$player->sneaking = false;
$interact($bamboo, PlayerInteractEvent::RIGHT_CLICK_BLOCK);
if (!$bamboo->isThick() || !str_contains($player->tip, 'Thick')) {
	throw new RuntimeException('Bamboo thick state did not toggle or report its value');
}
$checkWrite($world, $bamboo, 3);

$player->allowed = false;
$interact($bamboo, PlayerInteractEvent::LEFT_CLICK_BLOCK);
if (!str_contains($player->message, 'permission')) {
	throw new RuntimeException('Permission gate failed');
}
$checkWrite($world, $bamboo, 3);

$player->allowed = true;
$ordinaryStick = VanillaItems::STICK();
$ordinaryEvent = new PlayerInteractEvent($player, $ordinaryStick, $bamboo, null, Facing::UP, PlayerInteractEvent::LEFT_CLICK_BLOCK);
$plugin->onPlayerInteract($ordinaryEvent);
if ($ordinaryEvent->isCancelled()) {
	throw new RuntimeException('Ordinary stick interaction was cancelled');
}
$checkWrite($world, $bamboo, 3);

echo "PASS: DebugStick block state changes and permission gate\n";
