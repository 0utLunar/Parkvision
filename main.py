import cv2
import pickle
import cvzone
import numpy as np
import mysql.connector

# Configuração da conexão com o banco de dados
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="parkvision"
)

cursor = db.cursor()

# Video feed
cap = cv2.VideoCapture('rtsp://admin:@192.168.47.159')

with open('CarParkPos', 'rb') as f:
    posList = pickle.load(f)

width, height = 80, 30

def get_status_from_db(vaga_numero):
    sql = "SELECT status_vaga FROM vagas WHERE numero_vaga = %s"
    val = (vaga_numero,)
    cursor.execute(sql, val)
    result = cursor.fetchone()
    return result[0] if result else None

def update_database(vaga_numero, status):
    sql = "UPDATE vagas SET status_vaga = %s WHERE numero_vaga = %s"
    val = (status, vaga_numero)
    cursor.execute(sql, val)
    db.commit()

def checkParkingSpace(imgPro):
    spaceCounter = 0

    for i, pos in enumerate(posList):
        x, y = pos

        imgCrop = imgPro[y:y + height, x:x + width]
        count = cv2.countNonZero(imgCrop)

        vaga_numero = i + 1
        current_status = get_status_from_db(vaga_numero)

        if current_status == "reserved":
            # Se a vaga estiver reservada, apenas mude para occupied se ocupada
            if count >= 100:
                update_database(vaga_numero, "occupied")
                color = (0, 0, 255)
                thickness = 2
            else:
                color = (255, 165, 0)  # cor laranja para indicar reserva
                thickness = 5
        else:
            # Se a vaga não estiver reservada, siga a lógica padrão
            if count < 100:
                color = (0, 255, 0)
                thickness = 5
                status = "free"
            else:
                color = (0, 0, 255)
                thickness = 2
                status = "occupied"

            update_database(vaga_numero, status)

        cv2.rectangle(img, pos, (pos[0] + width, pos[1] + height), color, thickness)
        cvzone.putTextRect(img, str(count), (x, y + height - 3), scale=1,
                           thickness=2, offset=0, colorR=color)

        if current_status != "reserved" and status == "free":
            spaceCounter += 1

    cvzone.putTextRect(img, f'Livres: {spaceCounter}/{len(posList)}', (100, 50), scale=2,
                           thickness=2, offset=20, colorR=(0,200,0))

while True:

    if cap.get(cv2.CAP_PROP_POS_FRAMES) == cap.get(cv2.CAP_PROP_FRAME_COUNT):
        cap.set(cv2.CAP_PROP_POS_FRAMES, 0)
    success, img = cap.read()
    imgGray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    imgBlur = cv2.GaussianBlur(imgGray, (3, 3), 1)
    imgThreshold = cv2.adaptiveThreshold(imgBlur, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                         cv2.THRESH_BINARY_INV, 25, 16)
    imgMedian = cv2.medianBlur(imgThreshold, 5)
    kernel = np.ones((3, 3), np.uint8)
    imgDilate = cv2.dilate(imgMedian, kernel, iterations=1)

    checkParkingSpace(imgDilate)
    cv2.imshow("Image", img)
    cv2.waitKey(10)

# Fechar a conexão com o banco de dados ao finalizar
cursor.close()
db.close()
