import java.awt.*;
import java.awt.image.*;
import java.applet.*;
import java.util.*;
import java.net.*;

/**
 *
 * @author samuel
 */
public class SpriteTest extends Applet implements Runnable {
    int screenWidth = 640;
    int screenHeight = 480;
    int numAsteroids = 4;
    BufferedImage backbuffer;
    Graphics2D g2d;
    
    Sprite[] asteroid = new Sprite[numAsteroids];
    ImageEntity background;
    Thread gameloop;
    Random rand = new Random();
    
    public void init() {
        backbuffer = new BufferedImage(screenWidth, screenHeight, BufferedImage.TYPE_INT_RGB);
        g2d = backbuffer.createGraphics();
        
        background = new ImageEntity(this);
        background.load("bluespace.png");
        for(int s = 0; s < numAsteroids; s++)
        asteroid[s] = initAsteroidSprite();
        
    }
    
    public Sprite initAsteroidSprite() {
        Sprite ast = new Sprite(this,g2d);
        ast.setVelocity(new Point2D(rand.nextInt(4)-2,rand.nextInt(4)-2));
        ast.setRotationRate(2);
        ast.scale(new Point2D(0.1, 0.1));
        
        int width = screenWidth - ast.imageWidth() - 1;
        int height = screenHeight - ast.imageHeight() - 1;
        Point2D point = new Point2D(rand.nextInt(width), rand.nextInt(height));
        
        ast.setPosition(point);
        ast.load("asteroid2.png");
        return ast;
        
    }
    
    @Override
    public void start() {
        gameloop = new Thread(this);
        gameloop.start();
    }
    
    @Override
    public void stop() {
        gameloop = null;
    }
    
    @Override
    public void run() {
        Thread t = Thread.currentThread();
        while(t == gameloop){
            try{
                Thread.sleep(30);
            }
            catch(InterruptedException e) {
                e.printStackTrace();
            }
            repaint();
        }
    }
    
    public void update(Graphics g) {
        //draw background
        g2d.drawImage(background.getImage(), 0,0, screenWidth-1, screenHeight-1, this);
        for(int s=0; s < numAsteroids; s++) {
            for(int c=0; c < numAsteroids; c++) {
                if(c != s && asteroid[s].collidesWith(asteroid[c]) ) {
                    Point2D asv = asteroid[s].getVelocity();
                    asteroid[s].setVelocity(new Point2D(-asv.X(), -asv.Y()));
                }
            }
            asteroid[s].updatePosition();
            asteroid[s].updateRotation();
            asteroid[s].transform();
            asteroid[s].draw();
        }
        
        paint(g);
    }
    
    public void paint(Graphics g) {
        //draw the backbuffer to the screen
        g.drawImage(backbuffer, 0, 0, this);
    }
   
    
}
