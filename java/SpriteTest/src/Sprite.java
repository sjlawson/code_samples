import java.awt.*;
import java.applet.*;

/**
 *
 * @author samuel
 */
public class Sprite extends Object {
    
    private ImageEntity entity;
    protected Point2D pos;
    protected Point2D vel;
    protected double rotRate;
    protected int currentState;
    
    Sprite(Applet a, Graphics2D g2d) {
        entity = new ImageEntity(a);
        entity.setGraphics(g2d);
        entity.setAlive(false);
        pos = new Point2D(0, 0);
        vel = new Point2D(0, 0);
        rotRate = 0.0;
        currentState = 0;
    }
    
    public Point2D getVelocity() {
        return vel;
    }
    
    public void load(String filename){
        entity.load(filename);
    }
    
    public void scale(Point2D xyscale) {
         entity.setScale(xyscale);
    }
    
    //perform affine transformations
    public void transform(){
        entity.setX(pos.X());
        entity.setY(pos.Y());
        entity.transform();
    }
    
    public void draw(){
        entity.g2d.drawImage(entity.getImage(), entity.at, entity.applet);
    }
    
    //bounding box around sprite
    public void drawBounds(Color c){
        entity.g2d.setColor(c);
        entity.g2d.draw(getBounds());
    }
    
    public Rectangle getBounds() {
        return entity.getBounds(); 
    }

    public void updatePosition() {
    // based upon velocity
        pos.setX(pos.X() + vel.X());
        pos.setY(pos.Y() + vel.Y());
    }
    
    //methods related to automatic rotation
    public double rotationRate() { return rotRate; }
    public void setRotationRate(double rate) { rotRate = rate; }
    public void updateRotation() {
        setFaceAngle(faceAngle() + rotRate);
        if(faceAngle() < 0) {
            setFaceAngle(360 - rotRate);
        }else if(faceAngle() > 360){
            setFaceAngle(rotRate);
        }
    }
    
    public int state() { return currentState; }
    public void setState(int state){ currentState = state; }
    
    //sprite position
    public Point2D position() { return pos; }
    public void setPosition(Point2D pos) { this.pos = pos; }
    
    //sprite movement velocity
    public Point2D velocity() { return vel; }
    public void setVelocity(Point2D vel) { this.vel = vel; }
    
    public Point2D center() {
        return(new Point2D(entity.getCenterX(), entity.getCenterY()));
    }
    
    public boolean alive(){ return entity.isAlive(); }
    public void setAlive(boolean alive){entity.setAlive(alive);}
    
    public double faceAngle() { return entity.getFaceAngle(); }
    
    public void setFaceAngle(double angle) {
        entity.setFaceAngle(angle);
    }
    public void setFaceAngle(float angle) {
        entity.setFaceAngle((double)angle);
    }
    public void setFaceAngle(int angle) {
        entity.setFaceAngle((double)angle);
    }
    
    public double moveAngle() { return entity.getMoveAngle(); }
    public void setMoveAngle(double angle) {
        entity.setMoveAngle(angle);
    }
    public void setMoveAngle(float angle) {
        entity.setMoveAngle((double)angle);
    }
    public void setMoveAngle(int angle) {
        entity.setMoveAngle((double)angle);
    }
    
    public int imageWidth() { return entity.width(); }
    public int imageHeight() { return entity.height(); }
    
    //check for collision with a rect
    public boolean collidesWith(Rectangle rect){
        return (rect.intersects(getBounds()));
    }
    //another sprite
    public boolean collidesWith(Sprite sprite) {
        return (getBounds().intersects(sprite.getBounds()));
    }
    //collision with a point
    public boolean collidesWith(Point2D point) {
        return (getBounds().contains(point.X(), point.Y()));
    }
    
    public Applet applet() { return entity.applet; }
    public Graphics2D graphics() { return entity.g2d; }
    public Image image() { return entity.getImage(); }
    public void setImage(Image image) {
        entity.setImage(image);
    }
}