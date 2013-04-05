package my.datagrabber;

import java.awt.*;
import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;
import java.nio.charset.Charset;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;
import javax.imageio.ImageIO;

/**
 * Capture an area of the screen from user selection, save and use as input for Tesseract OCR engine, return OCRed text
 * @author samuel lawson, sjlawson@sdf.org
 */
public class DataGrabberOCRS {
    
    public static String DataGrabberOCRS() throws AWTException, InterruptedException, IOException {
        
      Toolkit toolkit = Toolkit.getDefaultToolkit();
        Dimension screenSize = toolkit.getScreenSize();
        Rectangle rectangle = new Rectangle(0, 0, screenSize.width, screenSize.height);
       
        Robot robot = new Robot();
        BufferedImage image = robot.createScreenCapture(rectangle);
        
        ScreenCaptureRectangle screenCaptureSelector = new ScreenCaptureRectangle(image);
        image = screenCaptureSelector.finalScreenCapture;
        //image = screenCaptureSelector.ScreenCaptureRectangle(image);
        
	if(image == null) {
            return null;
        }
        // BufferedImage scaledImage = DataGrabberOCRS.getScaledImage(image);
        String result = "";
         
        try {
            ImageIO.write(image, "png", new File("capture.png"));
             String[] cmd = { "/usr/bin/tesseract", "capture.png", "tess_out", "-psm 1" };
            Process p = Runtime.getRuntime().exec(cmd);
            p.waitFor();
                     
            List<String> lines = Files.readAllLines(Paths.get("tess_out.txt"),
                    Charset.defaultCharset());
           
            for(String line : lines) {
                result += line + "\n";
            }
            
            
        } catch (IOException ex) {
            Logger.getLogger(DataGrabberOCRS.class.getName()).log(Level.SEVERE, null, ex);
        }
           
            // System.out.println("OCRed Result: \n" + result + "\n END OF ORCed result.");
        return result;    
    }
    
    
}
