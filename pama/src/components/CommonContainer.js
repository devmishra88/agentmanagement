import * as React from "react";
import Card from "@mui/material/Card";
import CardContent from "@mui/material/CardContent";
import Typography from "@mui/material/Typography";
import { Button, CardHeader, CardActionArea, CardActions } from "@mui/material";

export default function CommonContainer({ ...props }) {
  const { addeddate, name, id } = props;

  return (
    <Card sx={{ maxWidth: `100%`,width:`100%`, mb: 1.5 }}>
      <CardActionArea>
        <CardHeader
          title={`${addeddate}`}
          sx={{
            background: `#1c4e80`,
            color: `#ffffff`,
          }}
        />
        <CardContent
          sx={{
            background: `#F1F1F1`,
          }}
        >
          <Typography gutterBottom variant="h5" component="div">
            {name}
          </Typography>
        </CardContent>
      </CardActionArea>
      <CardActions
        sx={{
          background: `#DADADA`,display:`flex`,justifyContent:`space-between`,
        }}
      >
        <Button size="small" color="primary">
          Edit
        </Button>
        <Button size="small" color="primary">
          Delete
        </Button>
      </CardActions>
    </Card>
  );
}
