import pygame
import math
import pcShip

class Moon(pygame.sprite.Sprite):
    def __init__(self):
        pygame.sprite.Sprite.__init__(self)
        self.image = pygame.image.load("moon-1.png")
        self.image = self.image.convert_alpha()
        self.rect = self.image.get_rect()
        self.rect.center = (600, 350)
    def update(self):
        self.rect.center = (600, 350)
        

class Shell(pygame.sprite.Sprite):
    def __init__(self, screen):
        pygame.sprite.Sprite.__init__(self)
        self.screen = screen
        
        self.image = pygame.Surface((10, 10))
        self.image.fill((0xff, 0xff, 0xff))
        self.image.set_colorkey((0xff, 0xff, 0xff))
        pygame.draw.circle(self.image, (0, 0, 0), (5, 5), 5)
        #self.image = pygame.transform.scale(self.image, (5, 5))
        self.rect = self.image.get_rect()
        self.rect.center = (-100, -100)
        
        self.speed = 0
        self.dir =0
        self.reset()
        
    def update(self):
        self.calcVector()
        self.calcPos()
        self.checkBounds()
        self.rect.center = (self.x, self.y)
   
    def calcVector(self):
        radians = self.dir * math.pi / 180
        
        self.dx = self.speed * math.cos(radians)
        self.dy = self.speed * math.sin(radians)
        self.dy *= -1
    
    def calcPos(self):
        self.x += self.dx
        self.y += self.dy
    
    def checkBounds(self):
        screen = self.screen
        if self.x > screen.get_width():
            self.reset()
        if self.x < 0:
            self.reset()
        if self.y > screen.get_height():
            self.reset()
        if self.y < 0:
            self.reset()
    
    def reset(self):
        """ move off stage and stop"""
        self.x = -100
        self.y = -100
        self.speed = 0

def main():
    screen = pygame.display.set_mode((1200, 700))
    pygame.display.set_caption("Star Cutter")
    
    background = pygame.Surface(screen.get_size())
    background.fill((0, 0, 0))
    
    ship = pcShip.Cutter(screen)
    shell = Shell(screen)
    moon = Moon()
    allSprites = pygame.sprite.Group(moon, ship, shell)
    
    
    clock = pygame.time.Clock()
    keepGoing = True
    while keepGoing:
        clock.tick(30)
        for event in pygame.event.get():
            if event.type == pygame.QUIT:
                keepGoing = False
            elif event.type == pygame.KEYDOWN:
                if event.key == pygame.K_LEFT:
                    ship.turnLeft()
                elif event.key == pygame.K_RIGHT:
                    ship.turnRight()
                elif event.key == pygame.K_UP:
                    ship.speedUp()
                elif event.key == pygame.K_DOWN:
                    ship.slowDown()
        
        
        allSprites.clear(screen, background)
        allSprites.update()
        allSprites.draw(screen)
        
        
        pygame.display.flip()
    
if __name__ == "__main__":
    main()